<?php

namespace MFrouh\ActivityModel\Interfaces;

interface ActivityInterface
{
    public function activityDefault(): array;

    public function activityChanges(): array;

    public function activityFcmTokens(): array;
}
