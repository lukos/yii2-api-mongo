<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\Token;
use yii\filters\auth\HttpBearerAuth;

/**
 * TokenController is an API controller for the Token model and uses Yii 
 * autowiring to provide a number of endpoints that can be used to CRUD Tokens.
 * The actions are automatically generated but if you need to override them in 
 * this class, you will need to handle e.g. different VERBs and the parameters.
 * 
 * ```
 * [
 *   'PUT,PATCH tokens/<id>' => 'token/update',
 *   'DELETE tokens/<id>' => 'token/delete',
 *   'GET,HEAD tokens/<id>' => 'token/view',
 *   'POST tokens' => 'token/create',
 *   'GET,HEAD tokens' => 'token/index',
 *   'tokens/<id>' => 'token/options',
 *   'tokens' => 'token/options',
 * ]
 * ```
 *
 * @author https://github.com/lukos
 */
class TokenController extends \yii\rest\ActiveController
{
    /**
     *
     * @var string Set the name of the Active model to use for CRUD
     */
    public $modelClass = Token::class;
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        // remove authentication filter if there is one
        unset($behaviors['authenticator']);

        // add CORS filter before authentication
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
        ];

        // Put in a bearer auth authentication filter
        // https://www.yiiframework.com/doc/api/2.0/yii-filters-auth-httpbearerauth
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
        ];
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];

        return $behaviors;
    }
    
    /**
    * Checks the privilege of the current user.
    *
    * This method should be overridden to check whether the current user has the privilege
    * to run the specified action against the specified data model.
    * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
    *
    * @param string $action the ID of the action to be executed
    * @param \yii\base\Model $model the model to be accessed. If `null`, it means no specific model is being accessed.
    * @param array $params additional parameters
    * @throws ForbiddenHttpException if the user does not have access
    */
    public function checkAccess($action, $model = null, $params = [])
    {
        // check if the user can access $action and $model
        // throw ForbiddenHttpException if access should be denied

        // THIS IS AN EXAMPLE, you will need to decide on your own authorization
        // logic or use rbac to handle this for you.
        // https://www.yiiframework.com/doc/guide/2.0/en/security-authorization#rbac
        //
        // if (!\Yii::$app->user->can('updateToken', ['token' => $model->_id])
        //   &&!\Yii::$app->user->can('deleteToken', ['token' => $model->_id]))
        //
        if ($action === 'update' || $action === 'delete') {
            if ($model->author_id !== \Yii::$app->user->id)
            {
                throw new \yii\web\ForbiddenHttpException(sprintf('You can only %s articles that you\'ve created.', $action));
            }
        }
    }
}
