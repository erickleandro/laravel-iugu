<?php

namespace Potelo\GuPayment;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Console\Exception\LogicException;

class Subscription extends Model
{
    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'trial_ends_at', 'ends_at',
        'created_at', 'updated_at',
    ];

    protected $iuguSubscriptionModelIdColumn;

    protected $iuguSubscriptionModelPlanColumn;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = getenv('GUPAYMENT_SIGNATURE_TABLE') ?: config('services.iugu.signature_table', 'subscriptions');
        $this->iuguSubscriptionModelIdColumn = getenv('IUGU_SUBSCRIPTION_MODEL_ID_COLUMN') ?: config('services.iugu.subscription_model_id_column', 'iugu_id');
        $this->iuguSubscriptionModelPlanColumn = getenv('IUGU_SUBSCRIPTION_MODEL_PLAN_COLUMN') ?: config('services.iugu.subscription_model_plan_column', 'iugu_plan');
    }

    /**
     * Get the user that owns the subscription.
     */
    public function user()
    {
        $model = getenv('IUGU_MODEL') ?: config('services.iugu.model');
        $column = getenv('IUGU_MODEL_FOREIGN_KEY') ?: config('services.iugu.model_foreign_key', 'user_id');

        if (in_array("Illuminate\Database\Eloquent\SoftDeletes", class_uses($model))) {
            return $this->belongsTo($model, $column)->withTrashed();
        }

        return $this->belongsTo($model, $column);
    }

    /**
     * Determine if the subscription is active, on trial, or within its grace period.
     *
     * @return bool
     */
    public function valid()
    {
        return $this->active() || $this->onTrial() || $this->onGracePeriod();
    }

    /**
     * Determine if the subscription is active.
     *
     * @return bool
     */
    public function active()
    {
        return is_null($this->ends_at) || $this->onGracePeriod();
    }

    /**
     * Determine if the subscription is no longer active.
     *
     * @return bool
     */
    public function cancelled()
    {
        return ! is_null($this->ends_at);
    }

    /**
     * Determine if the subscription is within its trial period.
     *
     * @return bool
     */
    public function onTrial()
    {
        if (! is_null($this->trial_ends_at)) {
            return Carbon::today()->lt($this->trial_ends_at);
        } else {
            return false;
        }
    }

    /**
     * Determine if the subscription is within its grace period after cancellation.
     *
     * @return bool
     */
    public function onGracePeriod()
    {
        if (! is_null($endsAt = $this->ends_at)) {
            return Carbon::now()->lt(Carbon::instance($endsAt));
        } else {
            return false;
        }
    }

    /**
     * Swap the subscription to a new Iugu plan.
     *
     * @param  string  $plan
     * @return $this
     */
    public function swap($plan)
    {
        $subscription = $this->asIuguSubscription();

        $subscription->change_plan($plan);

        //$this->user->invoice();


        $this->fill([
            $this->iuguSubscriptionModelPlanColumn => $plan,
            'ends_at' => null,
        ])->save();

        return $this;
    }

    /**
     * Cancel the subscription at the end of the billing period.
     *
     * @return $this
     */
    public function cancel()
    {
        $subscription = $this->asIuguSubscription();

        $subscription->suspend();

        // If the user was on trial, we will set the grace period to end when the trial
        // would have ended. Otherwise, we'll retrieve the end of the billing period
        // period and make that the end of the grace period for this current user.
        if ($this->onTrial()) {
            $this->ends_at = $this->trial_ends_at;
        } else {
            $this->ends_at = is_null($subscription->expires_at)
                ? Carbon::now()
                : Carbon::createFromFormat('Y-m-d', $subscription->expires_at);
        }

        $this->save();

        return $this;
    }

    /**
     * Cancel the subscription immediately.
     *
     * @return $this
     */
    public function cancelNow()
    {
        $subscription = $this->asIuguSubscription();

        $subscription->suspend();

        $this->markAsCancelled();

        return $this;
    }

    /**
     * Mark the subscription as cancelled.
     *
     * @return void
     */
    public function markAsCancelled()
    {
        $this->fill(['ends_at' => Carbon::now()])->save();
    }

    /**
     * Resume the cancelled subscription.
     *
     * @return $this
     *
     * @throws \LogicException
     */
    public function resume()
    {
        $subscription = $this->asIuguSubscription();

        $subscription->activate();

        // Finally, we will remove the ending timestamp from the user's record in the
        // local database to indicate that the subscription is active again and is
        // no longer "cancelled". Then we will save this record in the database.
        $this->fill(['ends_at' => null])->save();

        return $this;
    }


    /**
     * Get the subscription as a Iugu subscription object.
     *
     * @return \Iugu_Subscription
     */
    public function asIuguSubscription()
    {
        return $this->user->getIuguSubscription($this->{$this->iuguSubscriptionModelIdColumn});
    }
}
