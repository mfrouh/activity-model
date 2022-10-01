<?php

namespace MFrouh\ActivityModel\Traits;

use Exception;
use MFrouh\ActivityModel\Models\Activity;
use LaravelFCM\Facades\FCM;
use Illuminate\Support\Facades\Log;
use LaravelFCM\Message\OptionsBuilder;
use Illuminate\Database\QueryException;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;


trait ActivityModel
{

    public static function bootActivityModel()
    {
        if (auth()->user()) {
            try {
                $user_id = auth()->user()->id;
                static::created(function ($model) use ($user_id) {
                    $activity = $model->activities()->create($model->activityDefault()['created'] + ['user_id' => $user_id]);
                    $model->sendNotification($activity);
                });

                static::deleted(function ($model) use ($user_id) {
                    $activity = $model->activities()->create($model->activityDefault()['deleted'] + ['user_id' => $user_id]);
                    $model->sendNotification($activity);
                });

                static::updated(function ($model) use ($user_id) {
                    $changes = $model->getChanges();
                    if (count($changes) > 1 && $model->activityChanges()) {
                        foreach ($changes as $key => $value) {
                            if (array_key_exists($key, $model->activityChanges()) && array_key_exists('title_ar', $model->activityChanges()[$key])) {
                                $activity = $model->activities()->create($model->activityChanges()[$key] + ['user_id' => $user_id]);
                                $model->sendNotification($activity);
                            } else {
                                if (array_key_exists($key, $model->activityChanges()) && array_key_exists($value, $model->activityChanges()[$key]) && array_key_exists('title_ar', $model->activityChanges()[$key][$value])) {
                                    $activity = $model->activities()->create($model->activityChanges()[$key][$value] + ['user_id' => $user_id]);
                                    $model->sendNotification($activity);
                                }
                            }
                        }
                    } else {
                        $activity = $model->activities()->create($model->activityDefault()['updated'] + ['user_id' => $user_id, 'data' => json_encode($model->getChanges())]);
                        $model->sendNotification($activity);
                    }
                });
                if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses(new self))) {
                    static::restored(function ($model) use ($user_id) {
                        $activity = $model->activities()->create($model->activityDefault()['restored'] + ['user_id' => $user_id]);
                        $model->sendNotification($activity);
                    });
                }
            } catch (QueryException $error) {
                Log::alert($error);
            }
        }
    }

    public function activities()
    {
        return  $this->morphMany(Activity::class, 'activity');
    }

    public function sendNotification($activity)
    {
        $title = 'title_' . app()->getLocale();
        $message = 'message_' . app()->getLocale();
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60 * 20);
        $notificationBuilder = new PayloadNotificationBuilder($activity->$title);
        $notificationBuilder->setBody($activity->$message)
            ->setSound("general_notification.mp3");

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData([
            'data' => [
                'notification_type' => $activity->type,
                'notification_title' => $activity->$title,
                'notification_message' => $activity->$message,
                'notification_data' => [],
            ]
        ]);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();
        $tokens = $this->ActivityFcmUsers();

        if (count($tokens) > 0) {
            FCM::sendTo($tokens, $option, $notification, $data);
        }
    }
}
