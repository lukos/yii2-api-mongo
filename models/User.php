<?php

namespace app\models;

use yii\mongodb\ActiveRecord;
use yii\web\IdentityInterface;
use yii\filters\RateLimitInterface;
use yii\behaviors\TimestampBehavior;

class User extends ActiveRecord implements IdentityInterface, RateLimitInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;
    
    /**
     * @return string the name of the mongodb collection associated with this ActiveRecord class.
     */
    public static function collectionName()
    {
        return 'user';
    }
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
        ];
    }
    
    /**
     * @return array list of attribute names.
     */
    public function attributes()
    {
        return ['_id', 'username', 'email', 'address', 'status', 'access_token', 'auth_key', 'password_hash', 'password_reset_token', 'created_at', 'updated_at', 'rate_limit', 'allowance', 'allowance_updated_at'];
    }
    
    /**
     * When using API auth by bearer token, find which user the token belongs to
     * 
     * @param string $token
     * @param string $type
     * @return app\models\User
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['auth_key' => $token]);
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey() {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function getId() {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey) {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Find a user by their unique id
     * 
     * @param string $id
     * @return app\models\User
     */
    public static function findIdentity($id) {
        return static::findOne(['_id' => $id, 'status' => self::STATUS_ACTIVE]);
    }
    
    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }
    
    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }
    
    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
    
    //https://www.yiiframework.com/doc/guide/2.0/en/rest-rate-limiting
    /**
     * Lets the system know how many requests are allowed for this user in a given period of time
     * 
     * @param yii\web\Request $request
     * @param string $action
     * @return array
     */
    public function getRateLimit($request, $action)
    {
        // Tuple is num_requests, period_in_seconds
        return [$this->rate_limit, 1]; // $rateLimit requests per second
    }

    /**
     * Return the current rate limit values for this user
     * 
     * @param yii\web\Request $request
     * @param string $action
     * @return array
     */
    public function loadAllowance($request, $action)
    {
        return [$this->allowance, $this->allowance_updated_at];
    }

    /**
     * Update this user's rate limit allowances based on a request.
     * 
     * Note, this method can use the request and action to make decisions about
     * if and what allowance values to save
     * 
     * @param yii\web\Request $request The api request
     * @param string $action The action being called
     * @param int $allowance The updated allowance amount
     * @param int $timestamp The unix timestamp of the request
     */
    public function saveAllowance($request, $action, $allowance, $timestamp)
    {
        $this->allowance = $allowance;
        $this->allowance_updated_at = $timestamp;
        $this->save();
    }
}