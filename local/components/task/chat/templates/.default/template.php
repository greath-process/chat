<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?use \Bitrix\Main\Localization\Loc; CPullWatch::Add($USER->GetId(), 'PULL_TEST');?>
<div class="main-chat" id="chat_window" data-id="<?=$USER->GetID()?>">
<?if ($_POST) $APPLICATION->RestartBuffer();?>
<?
if(LANGUAGE_ID == 'ru') $rew_word = (substr($arResult['CHAT_INFO']['USER']['REVIEWS'], -1) == 1) ? 'отзыву' : 'отзывам';
if(LANGUAGE_ID == 'en') $rew_word = ($arResult['CHAT_INFO']['USER']['REVIEWS'] == 1) ? 'review' : 'reviews';
if(LANGUAGE_ID == 'es') $rew_word = ($arResult['CHAT_INFO']['USER']['REVIEWS'] == 1) ? 'reseña' : 'reseñas';
?>
  <div class="container chat">
    <div class="chat__list">
      <div class="chat__list-title"><?=getMessage('CHAT_MESSAGES');?>
        <div class="chat__list-count"><?=count($arResult['CHAT_LIST'])?></div>
      </div>
      <div class="chat__list-search">
        <input class="input" value="<?=$_REQUEST['word']?>" placeholder="Поиск">
      </div>
      <div class="chat__list-container perfectScrollbar">
        <?foreach($arResult['CHAT_LIST'] as $item):?><?if (strpos($_GET['id'],'_') !== false) $iden = explode("_", $_GET['id']); ?>
        <div class="chat__list-item <?if($iden[0] == $item['ID'] && $iden[1] == $item['PARTNER_ID']):?>is-active<?endif?>" id="chat-<?=$item['ID']?>" data-user="<?=$item['PARTNER_ID']?>"> 
          <div class="chat__list-icon">
            <?if(!empty($item['PERFORMER']) && $item['STATUS'] != 11):?>
                <div class="chat__list-check in-work">
                  <svg class="svg-image-in-work-dims">
                    <use xlink:href="/local/templates/taskme/images/sprites/main.stack.svg#image-in-work"></use>
                  </svg>
                </div>
            <?endif?>
            <?if($item['STATUS'] == 11 && !empty($item['PERFORMER'])):?>
                <div class="chat__list-check">
                  <svg class="svg-image-check-white-dims">
                    <use xlink:href="/local/templates/taskme/images/sprites/main.stack.svg#image-check-white"></use>
                  </svg>
                </div>
            <?endif?>
            <div class="auth__ring-label notification" style="display: none;width: 11px;height: 11px;border-radius: 50%;position: absolute;right: 0;top: -2px;background: white;z-index: 1;"></div>
            <div class="auth__ring-label notification" style="display: none;width: 9px;height: 9px;border-radius: 50%;position: absolute;right: 1px;top: -1px;background: #c341a3;z-index: 2;"></div>
            <div class="chat__list-thumb">
            <?if(!empty($item['PICTURE'])):?>
              <img src="<?=$item['PICTURE']?>" alt="<?=$item['NAME']?>">
            <?endif?>
            </div>
          </div>
          <div class="chat__list-info">
            <div class="chat__list-heading">
              <div class="chat__list-name"><?=$item['AUTHOR']?></div>
              <div class="chat__list-date"><?=FormatDate("d F", MakeTimeStamp($item['DATE']))?></div>
            </div>
            <div class="chat__list-title"><?=$item['NAME']?></div>
            <div class="chat__list-message"><?=$item['TEXT']?></div>
          </div>
        </div>
        <?endforeach?>
      </div>
    </div>
    <!-- right -->
    <?if(!empty($arResult['CHAT_ACTIVE'])):?>
    <div class="chat__messages <?if(!empty($_GET['id']) && !$_POST['word']):?>is-active<?endif?>">
      <div class="chat__messages-toggler">
        <div class="svg-image-toggle-open svg-image-toggle-open-dims"></div>
      </div>
      <div class="chat__messages-header">
        <div class="chat__backlink" id="backlinkRoot" <?/*onclick="window.location.href = document.location.pathname; return false;"*/?>>
          <div class="svg-image-arrow-backlink svg-image-arrow-backlink-dims"></div>
        </div>
        <div class="chat__messages-header-info">
          <div class="chat__list-item">
            <div class="chat__list-icon">
              <div class="chat__list-thumb">
              <?if(!empty($arResult['CHAT_INFO']['TASK']['PICTURE'])):?>
                <img src="<?=$arResult['CHAT_INFO']['TASK']['PICTURE']?>" alt="">
              <?endif?>
              </div>
            </div>
            <div class="chat__list-info">
              <div class="chat__list-heading">
                <div class="chat__list-name"><?=$arResult['CHAT_INFO']['USER']['NAME']?></div>
              </div>
              <div class="chat__list-title"><?=$arResult['CHAT_INFO']['TASK']['NAME']?></div>
            </div>
          </div>
        </div>
        <div class="chat__messages-header-user">
        <?if(!empty($arResult['CHAT_INFO']['USER']['PICTURE'])):?>
          <img src="<?=$arResult['CHAT_INFO']['USER']['PICTURE']?>" alt="">
        <?endif?>  
        </div>
        <div class="chat__messages-header-menu">
          <div class="dropdown"><a class="dropdown__btn chat__messages-header-menu-link" href="#"><span></span></a>
            <div class="dropdown__content">
              <ul>
                <li><a href="#">Menu 1</a></li>
                <li><a href="#">Menu 2</a></li>
                <li><a href="#">Menu 3</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
<!-- right -->
<!-- center -->
      <div class="chat__messages-list perfectScrollbar">
        <?foreach($arResult['CHAT_ACTIVE'] as $message):?>
        <?if($d != FormatDate('j F', $message['UF_DATE'])):?>
          <?$d = FormatDate('j F', $message['UF_DATE']);?>
          <div class="chat__messages-date"><span><?=$d?></span></div>
        <?endif?>
		  <?if(empty($message['UF_TEXT']) && empty($message['UF_FILE'])) continue;?>
          <div class="chat__message <?if(!empty($message['MY'])):?>chat__message-unswer<?endif?>">
            <div class="chat__message-container">
              <div class="chat__message-content"><?echo $message['UF_TEXT']?>
                <?if(!empty($message['UF_FILE']))
                {
                  $rsFile = CFile::GetByID($message['UF_FILE']); $arFile = $rsFile->Fetch();
                  echo '<br><a taget="_blank" href="'.CFile::GetPath($message['UF_FILE']).'"><div class="svg-image-add-file"></div>'.$arFile['ORIGINAL_NAME'].'</a>'; 
                }?>
              </div>
				<div class="chat__message-date"><?=(FormatDate('H:i', $message['UF_DATE']) != '00:00') ? FormatDate('H:i', $message['UF_DATE']) :'';?></div>
            </div>
          </div>
		  <?$hide_area = (isset($message['OFFER']) && !empty($message['OFFER'])) ? 'Y' : '';?>
        <?endforeach?>
      </div>
      <?if($arResult['CHAT_INFO']['TASK']['STATUS'] != 11 && $hide_area != 'Y'):?>
      <form class="chat__input" action="<?=$_SERVER['REQUEST_URI']?>" method="post">
        <textarea class="input chat__input-area" name="text" placeholder="<?=getMessage('CHAT_ENTER');?>"></textarea>
        <input type="hidden" name="add" value="Y">
        <input type="hidden" name="id" value="<?=$_GET['id'];?>">
        <div class="rel">
          <div class="chat__input-footer">
            <div class="chat__input-file">
              <?/*<label id="fileMessage">*/?>
                <input id="inputfile" name="file" type="file" ext="<?=$arParams['EXT']?>" max="<?=$arParams['FILESIZE']?>" <?/*accept="application/msword, application/vnd.ms-excel, application/vnd.ms-powerpoint,text/plain, application/pdf, image/*"*/?> style="display:none">
              <?/*</label>*/?>
              <div class="dz-preview dz-processing dz-image-preview dz-error dz-complete" id="hidden_file" style="display:none">
                <div class="dz-details">
                  <div class="dz-filename">
                    <span data-dz-name="">стемпинг.png</span>
                  </div>
                  <div class="dz-size" data-dz-size="">
                    <strong>0.6</strong> MB
                  </div>
                </div>
                <a class="dz-remove" href="javascript:undefined;" data-dz-remove="">
                  <div class="svg-image-remove-red svg-image-remove-red-dims"></div>
                </a>
              </div>
            </div>
            <div class="chat__input-controls">

              <label for="inputfile" class="btn-add-file">
                <div class="svg-image-add-file svg-image-add-file-dims"></div>
              </label>

              <a class="btn btn-green btn-send" href="javascript:void(0);">
                <div class="btn-icon">
                  <div class="svg-image-chat-send svg-image-chat-send-dims"></div>
                </div>
                <div class="btn-text"><?=getMessage('CHAT_SEND');?></div>
                <div class="btn-hover"></div>
              </a>
            </div>
          </div>
        </div>
      </form>
      <?endif?>
    </div>
    <!-- center -->
    <div class="chat__sidebar perfectScrollbar">
      <div class="chat__messages-header-menu">
        <div class="dropdown"><a class="dropdown__btn chat__messages-header-menu-link" href="#"><span></span></a>
          <div class="dropdown__content">
            <ul>
              <li><a href="#">Menu 1</a></li>
              <li><a href="#">Menu 2</a></li>
              <li><a href="#">Menu 3</a></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="chat__user-holder">
        <div class="chat__backlink chat__backlink-dialog">
          <div class="svg-image-arrow-backlink svg-image-arrow-backlink-dims"></div><?=getMessage('CHAT_DIALOG');?>
        </div>
        <div class="chat__user">
          <div class="chat__user-heading">
            <div class="chat__user-thumb">
            <?if(!empty($arResult['CHAT_INFO']['USER']['PICTURE'])):?>
              <img src="<?=$arResult['CHAT_INFO']['USER']['PICTURE']?>" alt="">
            <?endif?>
            </div>
            <div class="chat__user-name"><?=$arResult['CHAT_INFO']['USER']['NAME']?><span class="svg-image-user-verified svg-image-user-verified-dims"></span><br><?=$arResult['CHAT_INFO']['USER']['LAST_NAME']?></div>
          </div>
          <div class="chat__user-info">
            <div class="chat__user-info-city"><?=$arResult['CHAT_INFO']['USER']['COUNTRY']?> <?=$arResult['CHAT_INFO']['USER']['CITY']?></div>
            <div class="chat__user-info-time"><?if(!empty($arResult['CHAT_INFO']['USER']['ONLINE'])):?><?=getMessage('CHAT_STATUS');?><?else:?><?=getMessage('CHAT_STATUS_OLD');?> 
              <?=(FormatDate("Hago", MakeTimeStamp($arResult['CHAT_INFO']['USER']['LAST_DATE'])) != '0 hours ago') ? FormatDate("Hago", MakeTimeStamp($arResult['CHAT_INFO']['USER']['LAST_DATE'])) : FormatDate("iago", MakeTimeStamp($arResult['CHAT_INFO']['USER']['LAST_DATE']));?><?endif?></div>
          </div>
          <div class="chat__user-rating">
            <div class="chat__user-rating-value">
              <svg class="svg-image-star-dims">
                <use xlink:href="/local/templates/taskme/images/sprites/main.stack.svg#image-star"></use>
              </svg><b><?=round($arResult['CHAT_INFO']['USER']['RATE'], 1)?></b>
            </div>
            <div class="chat__user-rating-reviews"><a href="#"><?=getMessage('CHAT_PO');?> <?=(!empty($arResult['CHAT_INFO']['USER']['REVIEWS'])) ? $arResult['CHAT_INFO']['USER']['REVIEWS'] : '0'?> <?=$rew_word;?></a></div>
          </div>
          <div class="chat__user-tel">
            <div class="color-text"><?=getMessage('CHAT_TEL');?></div>
            <div class="chat__user-tel-number"><?=$arResult['CHAT_INFO']['USER']['PHONE']?></div>
          </div>
        </div>
      </div>
      <div class="chat__task-holder">
        <div class="chat__backlink chat__backlink-dialog">
          <div class="svg-image-arrow-backlink svg-image-arrow-backlink-dims"></div><?=getMessage('CHAT_DIALOG');?>
        </div>
        <div class="chat__task">
        <?if(!empty($arResult['CHAT_INFO']['TASK']['PICTURE'])):?>
          <div class="chat__task-thumb">
            <img src="<?=$arResult['CHAT_INFO']['TASK']['PICTURE']?>" alt="<?=$arResult['CHAT_INFO']['TASK']['NAME']?>">
          </div>
        <?endif?>
          <div class="chat__task-info">
            <div class="chat__task-title"><?=$arResult['CHAT_INFO']['TASK']['NAME']?></div>
            <div class="chat__task-cost"><?=(!empty($arResult['CHAT_INFO']['TASK']['PRICE'])) ? '€'.$arResult['CHAT_INFO']['TASK']['PRICE'] : 'Negotiable'?></div> 
            <div class="chat__task-city color-text"><?=$arResult['CHAT_INFO']['TASK']['PLACE']?></div>
            <div class="chat__task-time color-text"><?=getMessage('CHAT_CREATE');?> <?=date("d.m.Y", strtotime($arResult['CHAT_INFO']['TASK']['CREATE']))?></div>
			<div class="chat__task-start">  
				<span class="color-text chat__task-start-caption"><?=getMessage('CHAT_START');?>:</span> <?=date("j F", strtotime($arResult['CHAT_INFO']['TASK']['START']))?> <span class="color-text">/</span> <?=date("H:i", strtotime($arResult['CHAT_INFO']['TASK']['START']))?>
			</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?endif?>
  <template id="tplFileMessage">
    <div class="dz-preview dz-file-preview">
      <div class="dz-details">
        <div class="dz-filename"><span data-dz-name></span></div>
        <div class="dz-size" data-dz-size></div>
      </div><a class="dz-remove" href="javascript:undefined;" data-dz-remove="">
        <div class="svg-image-remove-red svg-image-remove-red-dims"></div></a>
    </div>
  </template>
<?if ($_POST) die();?>
</div>
<script>
// обновление чата через пуш
BX.ready(function () {
    BX.addCustomEvent("onPullEvent", function (module_id, command, params) {
		let id = $('.chat__list-item.is-active').attr('id'),
		parnter_id = $('.chat__list-item.is-active').data('user');
		id = parseInt(id.replace(/[^\d]/g, ''));
    if (module_id == "test" && command == 'check') {
      if (params.ID_TO) {
        if (params.ID_TO == $('#chat_window').data("id")) {
          $.ajax({
            type: "POST",
            data: { 'id': id, 'parnter_id': parnter_id },
            success: function (html) {
              let text = $('.chat__input textarea').val();
              $('#chat_window').html(html);
              if(text != undefined) $('.chat__input textarea').val(text);
              ReloadScript();
              $('#chat-'+params.ID_FROM+'').find('.notification').show();
            }
          });
        }
      }
    }
    });
    BX.PULL.extendWatch('PULL_TEST');
});
</script>