<?php

use yii\helpers\Url;

?>

<div class="modal fade" id="deadly299-gallery-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Кроп изображения</h4>
            </div>
            <div class="modal-body crop-modal-body">
                <img src="<?= $model->getUrl() ?>" width="100%" data-role="cropbox-img" id="cropbox"/>

                <input type="hidden" class="cord-crop" id="heightImage" name="heightImage"/>
                <input type="hidden" class="cord-crop" id="widthImage" name="widthImage"/>
                <input type="hidden" class="cord-crop" id="marginLeft" name="marginLeft"/>
                <input type="hidden" class="cord-crop" id="marginTop" name="marginTop"/>
                <input type="hidden" class="cord-crop" id="widthPlane" name="widthPlane"/>
                <input type="hidden" class="cord-crop" id="heightPlane" name="heightPlane"/>
                <input type="hidden" value="<?= $model->id ?>" id="id-image" name="id"/>
                <div class="btn-group crop-tools" data-id="<?= $model->id ?>"
                     data-url="<?= Url::to(['default/rotate-image']) ?>">
                    <button type="button" class="btn btn-primary" data-degrees="90" data-role="rotate-image">
                        <span class="fa fa-rotate-left"></span>
                    </button>
                    <button type="button" class="btn btn-primary" data-degrees="270" data-role="rotate-image">
                        <span class="fa fa-rotate-right"></span>
                    </button>
                    <button type="button" class="btn btn-primary" data-role="send-crop-image"
                            data-url="<?= Url::to(['default/crop-image']) ?>">
                        <span class="fa fa-crop"></span>
                    </button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal">
                        Отмена
                    </button>
                    <div class="preloadr-crop"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('#deadly299-gallery-modal').on('hide.bs.modal', function () {
        $('img#cropbox').imgAreaSelect({
            hide: true,
        });
        $('.block-crop-lib').html(null);
    });
</script>