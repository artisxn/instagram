<?php

namespace InetStudio\Instagram\Models;

use Spatie\MediaLibrary\Media;
use Emojione\Emojione as Emoji;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMediaConversions;

/**
 * Модель пользователя в инстаграме.
 *
 * Class InstagramUser
 *
 * @property int $id
 * @property string $pk
 * @property string $username
 * @property string $full_name
 * @property string $profile_pic_url
 * @property int $follower_count
 * @property int $following_count
 * @property int $media_count
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\InetStudio\Instagram\Models\InstagramCommentModel[] $comments
 * @property-read mixed $user_full_name
 * @property-read string $user_nickname
 * @property-read string $user_u_r_l
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\MediaLibrary\Media[] $media
 * @property-read \Illuminate\Database\Eloquent\Collection|\InetStudio\Instagram\Models\InstagramPostModel[] $posts
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\InetStudio\Instagram\Models\InstagramUserModel onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\InetStudio\Instagram\Models\InstagramUserModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\InetStudio\Instagram\Models\InstagramUserModel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\InetStudio\Instagram\Models\InstagramUserModel whereFollowerCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\InetStudio\Instagram\Models\InstagramUserModel whereFollowingCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\InetStudio\Instagram\Models\InstagramUserModel whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\InetStudio\Instagram\Models\InstagramUserModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\InetStudio\Instagram\Models\InstagramUserModel whereMediaCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\InetStudio\Instagram\Models\InstagramUserModel wherePk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\InetStudio\Instagram\Models\InstagramUserModel whereProfilePicUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\InetStudio\Instagram\Models\InstagramUserModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\InetStudio\Instagram\Models\InstagramUserModel whereUsername($value)
 * @method static \Illuminate\Database\Query\Builder|\InetStudio\Instagram\Models\InstagramUserModel withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\InetStudio\Instagram\Models\InstagramUserModel withoutTrashed()
 * @mixin \Eloquent
 */
class InstagramUserModel extends Model implements HasMediaConversions
{
    use SoftDeletes;
    use HasMediaTrait;

    /**
     * Связанная с моделью таблица.
     *
     * @var string
     */
    protected $table = 'instagram_users';

    /**
     * Атрибуты, для которых разрешено массовое назначение.
     *
     * @var array
     */
    protected $fillable = [
        'pk', 'username', 'full_name', 'profile_pic_url', 'follower_count', 'following_count', 'media_count',
    ];

    /**
     * Атрибуты, которые должны быть преобразованы в даты.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Загрузка модели
     * Событие удаления пользователя инстаграм.
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($user) {
            $user->posts()->delete();
            $user->comments()->delete();
        });
    }

    /**
     * Отношение "один ко многим" с моделью поста в инстаграме.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany(InstagramPostModel::class, 'user_pk', 'pk');
    }

    /**
     * Отношение "один ко многим" с моделью комментария в инстаграме.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(InstagramCommentModel::class, 'user_pk', 'pk');
    }

    /**
     * Получаем никнейм пользователя инстаграм.
     *
     * @return string
     */
    public function getUserNicknameAttribute()
    {
        return ($this->username) ? '@'.trim($this->username, '@') : '';
    }

    /**
     * Получаем ссылку на профиль пользователя инстаграм.
     *
     * @return string
     */
    public function getUserURLAttribute()
    {
        return 'https://instagram.com/'.trim($this->username, '@');
    }

    /**
     * Получаем имя пользователя.
     *
     * @return mixed
     */
    public function getUserFullNameAttribute()
    {
        return ($this->full_name) ? Emoji::shortnameToUnicode($this->full_name) : $this->user_nickname;
    }

    /**
     * Получаем id пользователя в соц.сети.
     *
     * @return mixed
     */
    public function getUserIdAttribute()
    {
        return $this->pk;
    }

    /**
     * Регистрируем преобразования изображений.
     *
     * @param Media|null $media
     *
     * @throws InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null)
    {
        $quality = (config('instagram.images.quality')) ? config('instagram.images.quality') : 75;

        if (config('instagram.images.users.conversions')) {
            foreach (config('instagram.images.users.conversions') as $collection => $image) {
                foreach ($image as $crop) {
                    foreach ($crop as $conversion) {
                        $imageConversion = $this->addMediaConversion($conversion['name'])->nonQueued();

                        if (isset($conversion['size']['width'])) {
                            $imageConversion->width($conversion['size']['width']);
                        }

                        if (isset($conversion['size']['height'])) {
                            $imageConversion->height($conversion['size']['height']);
                        }

                        if (isset($conversion['fit']['width']) && isset($conversion['fit']['height'])) {
                            $imageConversion->fit('max', $conversion['fit']['width'], $conversion['fit']['height']);
                        }

                        if (isset($conversion['quality'])) {
                            $imageConversion->quality($conversion['quality']);
                            $imageConversion->optimize();
                        } else {
                            $imageConversion->quality($quality);
                        }

                        $imageConversion->performOnCollections($collection);
                    }
                }
            }
        }
    }
}
