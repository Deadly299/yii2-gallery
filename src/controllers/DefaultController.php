<?php
namespace deadly299\gallery\controllers;

use yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use deadly299\gallery\models\Image;

class DefaultController extends Controller
{
        public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'ajax' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => $this->module->adminRoles
                    ]
                ]
            ]
        ];
    }

    
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionCropModal($id)
    {
        $model = $this->findImage($id);

        return $this->renderPartial('modalCrop', [
            'model' => $model,
            'post' => yii::$app->request->post(),
        ]);
    }

    public function actionRotateImage()
    {
        $dataPost = Yii::$app->request->post();
        $degrees = $dataPost['degrees'];
        $model = Image::findOne($dataPost['id']);
        $path = $model->getPathToOrigin();

        $this->createRotateImage($path, $degrees, 100) ;
        $model->clearCache();
        $responseData = [
            'error' => false,
            'response' => $model->getUrl(),
        ];

        return yii\helpers\Json::encode($responseData);
    }

    public function actionCropImage()
    {
        $dataPost = Yii::$app->request->post();
        $model = Image::findOne($dataPost['id']);
        $path = $model->getPathToOrigin();

        $sizesOriginalImage = $this->getSizesSidesImage($model->getPath());
        $settings = $this->getProportions($dataPost, $sizesOriginalImage['width'], $sizesOriginalImage['height']);
        $cropImage = $this->createCropImage($path, $settings, 100);

        $model->clearCache();
        $responseData = [
            'error' => false,
            'response' => $model->getUrl(),
        ];

        return yii\helpers\Json::encode($responseData);
    }

    private function createRotateImage($path, $degrees, $quality)
    {
        $type = pathinfo($path, PATHINFO_EXTENSION);

        switch ($type) {
            case 'jpeg':
            case 'jpg': {
                $resource = imagecreatefromjpeg($path);
                $rotate = imagerotate($resource, $degrees, 0);
                $result = imagejpeg($rotate, $path, $quality);
                break;
            }
            case 'png': {
                $resource = imagecreatefrompng($path);
                imagesavealpha($resource, true);
                $rotate = imagerotate($resource, $degrees, 0);
                $quality = (int)(100 - $quality) / 10 - 1;
                $result = imagepng($rotate, $path, $quality);
                break;

            }
            case 'gif': {
                $resource = imagecreatefromgif($path);
                $rotate = imagerotate($resource, $degrees, 0);
                $result = imagegif($rotate, $path);
                break;
            }

            default:
                return false;
        }

        return $result;
    }

    private function createCropImage($path, $settings, $quality)
    {
        $type = pathinfo($path, PATHINFO_EXTENSION);

        switch ($type) {
            case 'jpeg':
            case 'jpg': {
                $resource = imagecreatefromjpeg($path);
                $canvas = $this->createCanvasUnderImage($settings, $resource);
                $result = imagejpeg($canvas, $path, $quality);
                break;
            }
            case 'png': {
                $resource = imagecreatefrompng($path);
                imagesavealpha($resource, true);
                $canvas = $this->createCanvasUnderImage($settings, $resource);
                $quality = (int)(100 - $quality) / 10 - 1;
                $result = imagepng($canvas, $path, $quality);
                break;
            }
            case 'gif': {
                $resource = imagecreatefromgif($path);
                $result = imagegif($resource, $path);
                break;
            }
            default:
                return false;
        }

        return $result;
    }

    private function createCanvasUnderImage($settings, $resource)
    {
        $canvas = ImageCreateTrueColor( $settings['widthImageInPixel'], $settings['heightImageInPixel'] );
        imagecopyresampled(
            $canvas,
            $resource,
            0,
            0,
            $settings['marginLeftInPixel'],
            $settings['marginTopInPixel'],
            $settings['widthImageInPixel'],
            $settings['heightImageInPixel'],
            $settings['widthImageInPixel'],
            $settings['heightImageInPixel']
        );

        return $canvas;
    }

    private function  getSizesSidesImage($image)
    {
        $imageInfo = getimagesize($image);

        if($imageInfo) {
            return [
                'width' => $imageInfo[0],
                'height' => $imageInfo[1],
            ];
        }

        return false;
    }

    private function getProportions($data, $widthOriginalImage, $heightOriginalImage)
    {
        $widthImageInPercent = 100 / ($data['widthImage'] / $data['widthPlane']);
        $heightImageInPercent = 100 / ($data['heightImage'] / $data['heightPlane']);

        $marginLeftInPercent = ($data['marginLeft'] * 100) / $data['widthImage'];
        $marginTopInPercent = ($data['marginTop'] * 100) / $data['heightImage'];

        $widthImageInPixel = ($widthOriginalImage * $widthImageInPercent) / 100;
        $heightImageInPixel = ($heightOriginalImage * $heightImageInPercent) / 100;

        $marginLeftInPixel = ($widthOriginalImage * $marginLeftInPercent) / 100;
        $marginTopInPixel = ($heightOriginalImage * $marginTopInPercent) / 100;

        return [
            'widthImageInPixel' => $widthImageInPixel,
            'heightImageInPixel' => $heightImageInPixel,
            'marginLeftInPixel' => $marginLeftInPixel,
            'marginTopInPixel' => $marginTopInPixel,
        ];
    }
            
    public function actionModal($id)
    {
        $model = $this->findImage($id);
        
        return $this->renderPartial('modalAdd', [
            'model' => $model,
            'post' => yii::$app->request->post(),
        ]);
    }

    public function actionWrite($id)
    {
        $model = $this->findImage($id);
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->returnJson('success');
        }
        
        return $this->returnJson('false', 'Model or Image not found');
    }

    public function actionDelete($id)
    {
        $model = $this->findImage($id);
        $model->delete();
        
        return $this->returnJson('success');
    }
    
    public function actionSetmain($id)
    {
        $model = $this->findImage($id);
        $model->isMain = 1;
        $model->save(false);
        
        return $this->returnJson('success');
    }
    
    private function returnJson($result, $error = false)
    {
        $json = ['result' => $result, 'error' => $error];
        
        return Json::encode($json);
    }

    protected function findImage($id)
    {
        if(!$model = Image::findOne($id)) {
            throw new \yii\web\NotFoundHttpException("Image dont found.");
        }
        
        return $model;
    }
}