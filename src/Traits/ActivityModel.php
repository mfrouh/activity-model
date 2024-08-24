<?php

namespace MFrouh\ActivityModel\Traits;

use Exception;
use MFrouh\ActivityModel\Models\Activity;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use DateTimeImmutable;
use Kreait\Firebase\Exception\Messaging\NotFound as MessagingNotFound;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;


trait ActivityModel
{

    public static function bootActivityModel()
    {
        try {
            static::created(function ($model) {
                if (array_key_exists('created', $model->activityDefault())) {
                    $activity = $model->activities()->create($model->activityDefault()['created'] + ['user_id' => auth()->user() ? auth()->user()->id : null]);
                    $model->sendNotification($activity);
                }
            });

            static::deleted(function ($model) {
                if (array_key_exists('deleted', $model->activityDefault())) {
                    $activity = $model->activities()->create($model->activityDefault()['deleted'] + ['user_id' => auth()->user() ? auth()->user()->id : null]);
                    $model->sendNotification($activity);
                }
            });

            static::updated(function ($model) {
                $changes = $model->getChanges();
                $original = $model->getOriginal();
                $array = [];
                foreach ($changes as $key => $value) {
                    if ($key != 'updated_at') {
                        $array[$key] = ['new' => $value, 'old' => $original[$key]];
                    }
                }
                if (count($changes) > 1 && $model->activityChanges()) {
                    foreach ($changes as $key => $value) {
                        if (array_key_exists($key, $model->activityChanges()) && array_key_exists('title_ar', $model->activityChanges()[$key])) {
                            $activity = $model->activities()->create($model->activityChanges()[$key] + ['user_id' => auth()->user() ? auth()->user()->id : null, 'data' => json_encode($array)]);
                            $model->sendNotification($activity);
                        } else {
                            if (array_key_exists($key, $model->activityChanges()) && array_key_exists($value, $model->activityChanges()[$key]) && array_key_exists('title_ar', $model->activityChanges()[$key][$value])) {
                                $activity = $model->activities()->create($model->activityChanges()[$key][$value] + ['user_id' => auth()->user() ? auth()->user()->id : null, 'data' => json_encode($array)]);
                                $model->sendNotification($activity);
                            }
                        }
                    }
                } else {
                    if (array_key_exists('updated', $model->activityDefault())) {
                        $activity = $model->activities()->create($model->activityDefault()['updated'] + ['user_id' => auth()->user() ? auth()->user()->id : null, 'data' => json_encode($array)]);
                        $model->sendNotification($activity);
                    }
                }
            });
            if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses(new self))) {
                static::restored(function ($model) {
                    if (array_key_exists('restored', $model->activityDefault())) {
                        $activity = $model->activities()->create($model->activityDefault()['restored'] + ['user_id' => auth()->user() ? auth()->user()->id : null]);
                        $model->sendNotification($activity);
                    }
                });
            }
        } catch (QueryException $error) {
            Log::alert($error);
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

        try {
            $factory        = (new Factory())->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));
            $factory->createAuth();
            $cloudMessaging = $factory->createMessaging();
            $notification   = FcmNotification::create($activity->$title, $activity->$message);

            $data = [
                'data' => json_encode([
                    'notification_type'    => $activity->type,
                    'notification_title'   => $activity->$title,
                    'notification_message' => $activity->$message,
                    'notification_data'    => [],
                    'item_id'              => $activity->item_id,
                ]),
            ];

            if (count($tokens) > 0) {
                foreach ($tokens as $token) {
                    $message = CloudMessage::withTarget('token', $token)->withNotification($notification)->withData($data);

                    $cloudMessaging->send($message);
                }
            }
        } catch (MessagingNotFound $e) {
            Log::error('The target device could not be found.' . $e->getMessage());
        } catch (\Kreait\Firebase\Exception\Messaging\InvalidMessage $e) {
            Log::error('The given message is malformatted.' . $e->getMessage());
        } catch (\Kreait\Firebase\Exception\Messaging\ServerUnavailable $e) {
            $retryAfter = $e->retryAfter();
            Log::error('The FCM servers are currently unavailable. Retrying at ' . $retryAfter->format(\DATE_ATOM));
            while ($retryAfter <= new DateTimeImmutable()) {
                sleep(1);
            }
            $cloudMessaging->send($message);
        } catch (\Kreait\Firebase\Exception\Messaging\ServerError $e) {
            Log::error('The FCM servers are down.');
        } catch (MessagingException $e) {
            Log::error('Unable to send message: ' . $e->getMessage());
        } catch (\Throwable $th) {
            Log::error($th->getMessage() . '  from ' . $th->getFile() . ' on line ' . $th->getLine());
        }
    }
}
