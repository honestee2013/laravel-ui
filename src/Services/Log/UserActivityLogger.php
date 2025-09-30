<?php


namespace QuickerFaster\LaravelUI\Services\Log;



class UserActivityLogger
{
    public static function logAction($userId, $action, $model = null, $modelId = null, $ipAddress = null, $userAgent = null)
    {
        /*UserActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'ip_address' => $ipAddress ?: request()->ip(),
            'user_agent' => $userAgent ?: request()->header('User-Agent'),
        ]);*/
    }
}
