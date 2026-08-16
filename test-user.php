<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$user = App\Models\User::find(34);
echo json_encode([
    'subscription_plan' => $user->subscription_plan,
    'subscription_type' => $user->subscription_type,
    'roles' => $user->getRoleNames(),
    'active_subscriptions' => App\Models\Subscription::where('user_id', 34)->where('status', 'active')->get()->toArray(),
    'collaborations' => App\Models\WorkspaceCollaborator::where('user_id', 34)->where('status', 'active')->where('is_paused', false)->get()->toArray()
], JSON_PRETTY_PRINT);
