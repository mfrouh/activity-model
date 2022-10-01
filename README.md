### Activity Model

```php

use MFrouh\activityModel\Interfaces\ActivityInterface;
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
