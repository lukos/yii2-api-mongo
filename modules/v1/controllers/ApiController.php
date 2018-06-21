<?php

namespace app\modules\v1\controllers;

use yii\filters\auth\HttpBearerAuth;

/**
 * A generic API controller that implements its own actions explicitly and is not
 * explicitly bound to a model. This controller has routes automapped in the
 * web.php config file but most of these won't hit because we are extending
 * \yii\rest\Controller and not \yii\rest\ActiveController. You can either
 * block the unrequired actions in the rule or implement actions here to handle
 * them.
 * 
 * This default code only implements index which means the only request that will
 * work is GET/HEAD /apis, others will 404
 * 
 * @author https://github.com/lukos
 */
class ApiController  extends \yii\rest\Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        // Use HTTP Bearer Authentication
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
        ];
        return $behaviors;
    }
    
    /**
     * Simple action that just returns text that will be serialized to the 
     * format required by the request.
     * 
     * @return string
     */
    public function actionIndex()
    {
        return "Module controller";
    }
}
