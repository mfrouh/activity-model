# Activity Model

```composer
composer require mfrouh/activity-model
```

```bash
php artisan migrate
```

```env
FIREBASE_CREDENTIALS=firebase-credentials.json
```

```php

use MFrouh\ActivityModel\Interfaces\ActivityInterface;
use MFrouh\ActivityModel\Traits\ActivityModel;


class Order extends Model implements ActivityInterface
{
    use ActivityModel;

   public function activityChanges(): array
    {
        return [
            'status' => [
                'title_ar'   => '',
                'title_en'   => '',
                'message_ar' => '',
                'message_en' => '',
            ],
        ];
    }

    public function activityDefault(): array
    {
        return [
            'created' => [
                'title_ar'   => '',
                'title_en'   => '',
                'message_ar' => '',
                'message_en' => '',
            ],
            'deleted' => [
                'title_ar'   => '',
                'title_en'   => '',
                'message_ar' => '',
                'message_en' => '',
            ],
            'updated' => [
                'title_ar'   => '',
                'title_en'   => '',
                'message_ar' => '',
                'message_en' => '',
            ],
            'restored' => [
                'title_ar'   => '',
                'title_en'   => '',
                'message_ar' => '',
                'message_en' => '',
            ],
        ];
    }

    public function activityFcmTokens(): array
    {
        return [];
    }

}
```

## **1- activityChanges() method take column name messages**

## **2- activityDefault() method take event name messages**

## **3- activityFcmTokens() method take tokens to send fcm notification**
