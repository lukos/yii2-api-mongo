<?php

namespace app\modules\v1\models;

use yii\behaviors\TimestampBehavior;

/**
 * Example API model used with MongoDB database
 *
 * @author https://github.com/lukos
 */
class Token extends \yii\mongodb\ActiveRecord
{
    /**
     * @return string the name of the mongodb collection associated with this ActiveRecord class.
     */
    public static function collectionName()
    {
        return 'token';
    }
    
    /**
     * @return array list of attribute names to create properties for.
     */
    public function attributes()
    {
        return ['_id', 'type', 'value', 'created_at', 'updated_at', 'secret'];
    }
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),		// Auto timestamp created and updated properties
        ];
    }
    
    /**
     * Set rules for all properties that you want to set via the API endpoint
     * @return type
     */
    public function rules()
    {
        return [
            [['type','value','secret'], 'string'],
            [['created_at','updated_at'], 'integer'],
        ];
    }
    
    /**
     * Describes which fields to return for queries against a Token(s)
     * @return array
     */
    public function fields()
    {
        return [
            '_id',
            'type',
            'value',
            'created_at',
            'updated_at'
        ];
    }
}
