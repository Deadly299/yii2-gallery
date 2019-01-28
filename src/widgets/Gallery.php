<?php
namespace deadly299\gallery\widgets;

use yii;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\file\FileInput;

class Gallery extends \yii\base\Widget
{
    public $model = null;
    public $previewSize = '140x140';
    public $fileInputPluginLoading = true;
    public $fileInputPluginOptions = [];
    public $label = null;
    public $dataOptionsLink = [];

    public function init()
    {
        if(!$this->label) {
            $this->label = yii::t('gallery', 'Image');
        }
        
        $view = $this->getView();
        $view->on($view::EVENT_END_BODY, function($event) {
            echo $this->render('modal');
        });

        \deadly299\gallery\assets\GalleryAsset::register($this->getView());
    }

    public function run()
    {
        $model = $this->model;
        $params = [];
        $img = '';
        $label = '<label class="control-label">'. $this->label .'</label>';
        $cart = '';
        
        if($model->getGalleryMode() == 'single') {
            if($model->hasImage()) {
                $image = $this->model->getImage();
                $img = $this->getImagePreview($image);
                $params = $this->getParams($image->id);

            }

            return $label . '<br style="clear: both;" />' . Html::tag('div', $img, $params) . '<br style="clear: both;" />' . $this->getFileInput();
        }

        if (  $this->model->hasImage() ){
            $elements = $this->model->getImages();
            $cart = Html::ul(
                $elements,
                [
                    'item' => function($item) {
                        return $this->row($item);
                    },
                    'class' => 'deadly299-gallery'
                ]);
        }
        $modal = Html::tag('div', null, ['data-role' => 'modal-crop']);
        $blockCropLib = Html::tag('div', null, ['class' => 'block-crop-lib']);

        return Html::tag( 'div', $label . $cart . $blockCropLib . '<br style="clear: both;" />' . $this->getFileInput() . $modal);
    }

    private function row($image)
    {
        if($image instanceof \deadly299\gallery\models\PlaceHolder) {
            return '';
        }

        $class = ' deadly299-gallery-row';

        if($image->isMain) {
            $class .= ' main';
        }

        $liParams = $this->getParams($image->id);
        $liParams['class'] .=  $class;

        return Html::tag('li', $this->getImagePreview($image), $liParams);
    }

    private function getFileInput()
    {
        return FileInput::widget([
            'name' => $this->model->getInputName() . '[]',
            'options' => [
                'accept' => 'image/*',
                'multiple' => $this->model->getGalleryMode() == 'gallery',
            ],
            'pluginOptions' => $this->fileInputPluginOptions,
            'pluginLoading' => $this->fileInputPluginLoading
        ]);
    }

    private function getParams($id)
    {
        $model = $this->model;

        return  [
            'class' => 'deadly299-gallery-item',
            'data-model' => $model::className(),
            'data-id' => $model->id,
            'data-image' => $id
        ];
    }

    private function getImagePreview($image)
    {
        $size = (explode('x', $this->previewSize));

        $delete = Html::a('✖', '#', ['data-action' => Url::toRoute(['/gallery/default/delete', 'id' => $image->id]), 'class' => 'delete']);
        $crop = Html::a($this->getParamsIconCrop($image->id), false, ['class' => 'crop']);
        $write = Html::a('<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>', '#', ['data-action' => Url::toRoute(['/gallery/default/modal', 'id' => $image->id]), 'class' => 'write']);
        $img = Html::img($image->getUrl($this->previewSize), ['data-action' => Url::toRoute(['/gallery/default/setmain', 'id' => $image->id]), 'width' => $size[0], 'height' => $size[1], 'class' => 'thumb']);
        $a = Html::a($img, $image->getUrl(), $this->dataOptionsLink);

        return $delete.$crop.$write.$a;
    }

    private function getParamsIconCrop($id)
    {
        $params = [
            'class' => 'glyphicon glyphicon-retweet',
            'data-role' => 'show-modal-crop-icon',
            'data-action' => Url::to(['/gallery/default/crop-modal', 'id' => $id]),
        ];

        return Html::tag('span', null, $params);
    }
}
