<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\Grid\Declension;

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */

$this->setFrameMode(true);
global $USER;
$userId = $arResult['CREATED_BY'];
$taskName = $arResult['PROPERTIES']['TASK_'.strtoupper(LANGUAGE_ID).'_TITLE']['VALUE'];
$taskText = $arResult['PROPERTIES']['TASK_'.strtoupper(LANGUAGE_ID).'_TEXT']['VALUE']['TEXT'];
$taskPrice = $arResult['PROPERTIES']['TASK_PRICE']['VALUE'] > 0 ? $arResult['PROPERTIES']['TASK_PRICE']['VALUE'].'&euro;' : 'Цена договорная';
$noRisk = isset($arResult['PROPERTIES']['TASK_NORISK']['VALUE']) ? $arResult['PROPERTIES']['TASK_NORISK']['VALUE'] : false;
$taskAddress = isset($arResult['PROPERTIES']['TASK_ADDRESS']['VALUE']) ? $arResult['PROPERTIES']['TASK_ADDRESS']['VALUE'] : false;
$taskLocation = isset($arResult['PROPERTIES']['TASK_LOCATION']['VALUE']) ? $arResult['PROPERTIES']['TASK_LOCATION']['VALUE'] : false;
$taskStart = isset($arResult['PROPERTIES']['TASK_START_TIME']['VALUE']) ? $arResult['PROPERTIES']['TASK_START_TIME']['VALUE'] : false;
$arUser = $arResult['USER_DATA'];
//$taskerRating = isset($arUser['UF_RATING_TASKER']) ? round($arUser['UF_RATING_TASKER'],1) : '0.0';
$customerRating = isset($arUser['UF_RATING_CUSTOMER']) ? round($arUser['UF_RATING_CUSTOMER'],1) : '0.0';
$tasksCount = isset($arUser['UF_COUNT_TASKER']) ? $arUser['UF_COUNT_TASKER'] : 0;
$offersExist = Taskme::getOffers($arResult['ID'], $USER->GetID());
$offersTotal = Taskme::getOffers($arResult['ID']); 

?>
<?php //printr($arResult);?>
<section class="container task">
        <div class="page">
          <div class="page__content">
            <div class="task__container">
              <div class="task__content">
                <div class="task__heading"><a class="profilePublic__backlink" href="<?=SITE_DIR;?>tasks/">
                    <svg class="svg-image-arrow-back-dims">
                      <use xlink:href="<?=SITE_TEMPLATE_PATH;?>/images/sprites/main.stack.svg#image-arrow-back"></use>
                    </svg><?=getMessage('TASK_TASKS');?></a>
                  <div class="task__heading-flex">
                    <div class="task__heading-number"><span class="color-text"><?=getMessage('TASK_TASK');?></span>
                      № <?=$arResult['ID'];?>
                    </div>
                    <div class="dropdown share">
                      <div class="dropdown__btn">
                        <svg class="svg-image-share-default-dims">
                          <use xlink:href="<?=SITE_TEMPLATE_PATH;?>/images/sprites/main.stack.svg#image-share-default"></use>
                        </svg>
                      </div>
                      <div class="dropdown__content">
                        <?$APPLICATION->IncludeComponent(
                        'bitrix:main.share',
                        'taskme',
                        [
                            'HIDE' => 'N',
                            'HANDLERS' => ['facebook', 'twitter', 'vk'],
                            'PAGE_URL' => $APPLICATION->GetCurPage(),
                            'PAGE_TITLE' => $taskName,
                            'SHORTEN_URL_LOGIN' => '',
                            'SHORTEN_URL_KEY' => '',
                          ]
                          ); ?>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="task__title">
                  <h1 class="h2" data-uri="<?=$arResult['DETAIL_PAGE_URL'];?>"><?=$taskName;?></h1>
                </div>
                <div class="task__stats">
                  <div class="task__stats-list color-text">
                      <?php $posted = $arResult['DATE_CREATE'];
                      $posted = DateTime::createFromFormat('d.m.Y H:i:s', $posted); ?>
                    <div class="task__stats-item"><?=GetMessage('TASK_POSTED');?> <?=Taskme::ago($posted);?></div>
                    <?php $declension = new Declension(GetMessage('TASK_ELEMENT_VIEW_ONE'), GetMessage('TASK_ELEMENT_VIEW_TWO'), GetMessage('TASK_ELEMENT_VIEW_ALL')); ?>
                    <div class="task__stats-item"><?=$arResult['SHOW_COUNTER'] ? $arResult['SHOW_COUNTER'] : '0';?> <?=$declension->get($arResult['SHOW_COUNTER']);?></div>
                    <div class="task__stats-item"><?php echo implode(', ', $arResult['SECTIONS_NAME']); ?></div>
                  </div>
                    <?php
                      switch ($arResult['PROPERTIES']['TASK_STATUS']['VALUE']) {
                      case 'Открыто':
                        $taskStatus = true;
                        $statusClass = 'task__status_active';
                        break;
                      case 'Выполняется':
                        $statusClass = 'task__status_performed';
                        break;
                      case 'Выполнено':
                        $statusClass = 'task__status_finished';
                        break;
                    }
                    ?>
                  <div class="task__status <?=$statusClass;?>"><?=getMessage($arResult['PROPERTIES']['TASK_STATUS']['VALUE']);?></div>
                </div>
                <div class="task__info">
                  <?php if (
                    !empty($taskText)
                  || isset($arResult['DETAIL_PICTURE'])
                  || is_array($arResult['MORE_PHOTO'][0])
                  ) { ?>
                  <div class="task__info-row task__info-row_intro">
                    <div class="task__info-label"><?=getMessage('TASK_TASK');?></div>
                    <div class="task__info-value">
                      <div class="task__info-description"><?=$taskText;?></div>
                            <?php if (isset($arResult['DETAIL_PICTURE']) || is_array($arResult['MORE_PHOTO'][0])) { ?>
                                      <div class="task__slider">
                                        <div class="swiper-container" id="taskSlider">
                                          <div class="swiper-wrapper" id="taskSliderGallery">
                              <?php if (is_array($arResult['DETAIL_PICTURE'])) {
                      $mainImage = CFile::ResizeImageGet(
                          $arResult['DETAIL_PICTURE'],
                          [
                                    'width' => 200,
                                    'height' => 200
                                  ],
                          BX_RESIZE_IMAGE_EXACT
                      ); ?>
                            <div class="swiper-slide" data-src="<?=$arResult['DETAIL_PICTURE']['SRC']; ?>">
                              <div class="task__slider-slide main"><img src="<?=$mainImage['src']; ?>" alt="<?=$taskName; ?>" decoding="async" loading="lazy"></div>
                            </div>
                            <?php
                  } ?>
							              <?php if (count($arResult['MORE_PHOTO'])>1) {
                      foreach ($arResult['MORE_PHOTO'] as $arImage) {
                          $addImage = CFile::ResizeImageGet(
                              $arImage['ID'],
                              [
                                      'width' => 200,
                                      'height' => 200
                                    ],
                              BX_RESIZE_IMAGE_EXACT
                          ); ?>
									<div class="swiper-slide" data-src="<?=$arImage['SRC']; ?>">
                              			<div class="task__slider-slide"><img src="<?=$addImage['src']; ?>" alt="<?=$taskName; ?>" decoding="async" loading="lazy"></div>
                            		</div>
							<?php
                      }
                  }
                            ?>
                          </div>
                        </div>
                        <div class="swiper-buttons">
                          <div class="swiper-button swiper-button-prev">
                            <div class="svg-image-arrow-prev svg-image-arrow-prev-dims"></div>
                          </div>
                          <div class="swiper-button swiper-button-next">
                            <div class="svg-image-arrow-next svg-image-arrow-next-dims"></div>
                          </div>
                        </div>
                      </div>
					  <?php } ?>
                    </div>
                  </div>
                  <?php } ?>
                  <div class="task__info-row task__info-row_cost">
                    <div class="task__info-label">Стоимость</div>
                    <div class="task__info-value"><b><?=$taskPrice;?></b></div>
                  </div>
                  <div class="task__info-row task__info-row_pay">
                    <div class="task__info-label"><?=GetMessage('TASK_PAYMENT');?></div>
                    <div class="task__info-value">
					 <?php if ($noRisk) { ?>
                      <div class="riskFree color-text">
                        <div class="svg-image-protection svg-image-protection-dims"></div>Сделка без риска
                      </div>
					  <?php } else { ?>наличными<?php } ?>
                    </div>
                  </div>
				  <?php if ($taskAddress) { ?>
                  <div class="task__info-row task__info-row_address">
                    <div class="task__info-label"><?=getMessage('TASK_ADDRESS');?></div>
                    <div class="task__info-value"><?=$taskAddress;?>
						<?php if ($taskLocation) { ?>
                      		<p><a class="link-green popup-link" href="#popupMap">Посмотреть на карте</a></p>
					  	<?php } ?>
                    </div>
                  </div>
				  <?php } ?>
				  <?php if ($taskStart) { ?>
                  <div class="task__info-row task__info-row_start">
                    <div class="task__info-label">Приступить</div>
                    <div class="task__info-value"><?=FormatDate("d f", MakeTimeStamp($taskStart));?>
                      <?php if (strlen($taskStart)>10) { ?>
                      <span class="color-text">/</span> <?=FormatDate("h:m", MakeTimeStamp($taskStart));?>
                      <?php } ?>
                    </div>  
                  </div>
				  <?php } ?>
                </div>
              </div>
              <div class="task__sidebar">
                <div class="task__sidebar-content">
                  <?php 
                  if ($taskStatus && $userId !== $USER->GetID()) {
                    $buttonMessage = 'Предложить свои услуги';
                  ?>
                  <div class="infoBox infoBox-setTask">
                    <div class="infoBox__notice color-text"><?=$offersTotal>0 ? 'На эту задачу уже откликнулось '.$offersTotal.' исполнителей' : 'Пока не поступило предложений от исполнителей';?></div>
                    <?php if ($USER->IsAuthorized() && !$offersExist) { ?>
                      <a class="btn btn-green btn-flex popup-link" href="#offerServices">
                    <?php } else if($offersExist && $USER->GetID()>0) { 
                      $buttonMessage = 'Ваш отклик был отправлен';
                    ?>
                      <a class="btn btn-green btn-flex popup-link" href="#">
                    <?php } else { ?>
                      <a class="btn btn-green btn-flex popup-link" href="#login">
                    <?php } ?>
                      <div class="btn-text"><?=$buttonMessage;?></div>
                      <div class="btn-hover"></div>
                    </a>
                  </div>
                  <?php } ?>
                  <?php if (is_array($arUser)) { ?>
                  <div class="task__customer">
                    <div class="task__customer-image">
                      <? if ($arUser['PERSONAL_PHOTO']['src']) { ?>
                        <img src="<?=$arUser['PERSONAL_PHOTO']['src'];?>" alt="Заказчик">
                      <?php } ?>
                      <div class="svg-image-user-thumb svg-image-user-thumb-dims"></div>
                    </div>
                    <div class="task__customer-info">
                      <div class="color-text">Заказчик</div><a class="task__customer-name" href="<?=SITE_DIR;?>customers/<?=$arUser['ID'];?>/"><?=$arUser['NAME'].' '.substr($arUser['LAST_NAME'],0,1);?></a>
                      <div class="task__customer__rating">
                        <div class="task__customer__rating-value">
                          <svg class="svg-image-star-dims">
                            <use xlink:href="<?=SITE_TEMPLATE_PATH;?>/images/sprites/main.stack.svg#image-star"></use>
                          </svg><b><?=$customerRating;?></b>
                        </div>
                        <div class="task__customer__reviews"><a href="<?=SITE_DIR;?>customers/<?=$arUser['ID'];?>/"><?=$arResult['CUSTOMER_REVIEWS'];?> отзывов</a></div>
                      </div>
                      <div class="task__customer-tasks"><span class="color-text">Опубликованых заданий <?=$tasksCount;?></span>
                      </div>
                    </div>
                  </div>
                  <?php } ?>
                </div>
              </div>
            </div>
            <div class="task__similar">
              <div class="h2 task__similar-title">Похожие задания</div>
            </div>
            <div class="task__similar-list">
              <div class="swiper-container" id="taskSimilarSlider">
                <div class="swiper-wrapper">
                  <div class="swiper-slide">
                    <div class="taskCard">
                      <div class="taskCard__header">
                        <div class="taskCard__image">
                          <div class="taskCard__thumb"><img src="<?=SITE_TEMPLATE_PATH;?>/images/tmp/img-02.jpg" alt=""></div>
                          <div class="taskCard__cost taskCard__cost_mobile">€10 300</div>
                        </div><a class="taskCard__title" href="#">Нужен ремонт кухни в старом доме</a>
                        <div class="taskCard__cost taskCard__cost_desktop">€10 300</div>
                      </div>
                      <div class="taskCard__data">
                        <div class="taskCard__data-item">
                          <div class="taskCard__data-city">Малага, Гранада</div>
                        </div>
                        <div class="taskCard__data-item"><span class="taskCard__data-time">Создан 23:40</span><span class="taskCard__data-response">4 отклика</span></div>
                      </div>
                      <div class="taskCard__description">
                        <div class="rollUp" data-more="Развернуть" data-less="Свернуть">В частности, синтетическое тестирование предполагает независимые способы реализации анализа существующих паттернов поведения! Современные технологии достигли такого уровня ...</div>
                      </div>
                      <div class="taskCard__footer">
                        <div class="taskCard__start"><span class="color-text">Приступить:</span> 3 апреля <span class="color-text">/</span> 15:00</div>
                        <div class="riskFree color-text">
                          <div class="svg-image-protection svg-image-protection-dims"></div>Сделка без риска
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="swiper-slide">
                    <div class="taskCard">
                      <div class="taskCard__header">
                        <div class="taskCard__image">
                          <div class="taskCard__thumb"><img src="<?=SITE_TEMPLATE_PATH;?>/images/tmp/img-02.jpg" alt=""></div>
                          <div class="taskCard__cost taskCard__cost_mobile">€10 300</div>
                        </div><a class="taskCard__title" href="#">Нужен ремонт кухни в старом доме</a>
                        <div class="taskCard__cost taskCard__cost_desktop">€10 300</div>
                      </div>
                      <div class="taskCard__data">
                        <div class="taskCard__data-item">
                          <div class="taskCard__data-city">Малага, Гранада</div>
                        </div>
                        <div class="taskCard__data-item"><span class="taskCard__data-time">Создан 23:40</span><span class="taskCard__data-response">4 отклика</span></div>
                      </div>
                      <div class="taskCard__description">
                        <div class="rollUp" data-more="Развернуть" data-less="Свернуть">В частности, синтетическое тестирование предполагает независимые способы реализации анализа существующих паттернов поведения! Современные технологии достигли такого уровня ...</div>
                      </div>
                      <div class="taskCard__footer">
                        <div class="taskCard__start"><span class="color-text">Приступить:</span> 3 апреля <span class="color-text">/</span> 15:00</div>
                        <div class="riskFree color-text">
                          <div class="svg-image-protection svg-image-protection-dims"></div>Сделка без риска
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="swiper-slide">
                    <div class="taskCard">
                      <div class="taskCard__header">
                        <div class="taskCard__image">
                          <div class="taskCard__thumb"><img src="<?=SITE_TEMPLATE_PATH;?>/images/tmp/img-02.jpg" alt=""></div>
                          <div class="taskCard__cost taskCard__cost_mobile">€10 300</div>
                        </div><a class="taskCard__title" href="#">Нужен ремонт кухни в старом доме</a>
                        <div class="taskCard__cost taskCard__cost_desktop">€10 300</div>
                      </div>
                      <div class="taskCard__data">
                        <div class="taskCard__data-item">
                          <div class="taskCard__data-city">Малага, Гранада</div>
                        </div>
                        <div class="taskCard__data-item"><span class="taskCard__data-time">Создан 23:40</span><span class="taskCard__data-response">4 отклика</span></div>
                      </div>
                      <div class="taskCard__description">
                        <div class="rollUp" data-more="Развернуть" data-less="Свернуть">В частности, синтетическое тестирование предполагает независимые способы реализации анализа существующих паттернов поведения! Современные технологии достигли такого уровня ...</div>
                      </div>
                      <div class="taskCard__footer">
                        <div class="taskCard__start"><span class="color-text">Приступить:</span> 3 апреля <span class="color-text">/</span> 15:00</div>
                        <div class="riskFree color-text">
                          <div class="svg-image-protection svg-image-protection-dims"></div>Сделка без риска
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="swiper-buttons">
                <div class="swiper-button swiper-button-prev">
                  <div class="svg-image-arrow-prev svg-image-arrow-prev-dims"></div>
                </div>
                <div class="swiper-button swiper-button-next">
                  <div class="svg-image-arrow-next svg-image-arrow-next-dims"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <div class="popup popupOfferServices mfp-with-zoom mfp-hide" id="offerServices">
        <div class="popup__container">
          <div class="popup__title">Предложить свои услуги</div>
          <div class="popup__content">
            <textarea class="input" placeholder="Укажите свой опыт работы, расскажите, почему именно вы должны стать исполнителем этого задания"></textarea>
            <div class="popupOfferServices__buttons"><a class="btn btn-green btn-submit" data-customer="<?=$userId;?>" data-task="<?=$arResult['ID'];?>" data-tasker="<?=$USER->GetID();?>" href="#">
                <div class="btn-text">Отправить</div>
                <div class="btn-hover"></div></a></div>
          </div>
        </div>
      </div>
      <div class="popup popupOk mfp-with-zoom mfp-hide" id="popupOk">
        <div class="popup__container text-center">
          <div class="popupOk__symbol">
            <div class="svg-image-popup-ok svg-image-popup-ok-dims"></div>
          </div>
          <div class="popup__title">Предложение отправлено</div>
          <div class="popup__content color-text">Ожидайте ответа заказчика<br>Проверяйте входящие сообщения в личном кабинете или <a class="link-green" href="#">настройте уведомления</a>, что бы получать оповещения на email</div>
        </div>
      </div>
      <?php if (strlen($arResult['PROPERTIES']['TASK_LOCATION']['VALUE'])) { ?>
      <div class="popup popupMap mfp-with-zoom mfp-hide" id="popupMap">
        <div class="popup__map" id="map">
          <?php
            preg_match('#\((.*?)\)#', $arResult['PROPERTIES']['TASK_LOCATION']['VALUE'], $match);
            $arrLocations = explode(',', $match[1]);
            $longitude = trim($arrLocations[1]);
            $latitude = trim($arrLocations[0]);
          ?>
          <script defer src="//maps.googleapis.com/maps/api/js?key=AIzaSyBZfmX8i2zAJH5SwNXtkO5DgrEt5Rrj9MM&amp;callback=initMap"></script>
          <script>
            function initMap() {
                const place = {lat: <?=$latitude;?>, lng: <?=$longitude;?>};
                const map = new google.maps.Map(document.getElementById("map"), {
                    scaleControl: true,
                    center: place,
                    zoom: 12,
                });
                const infowindow = new google.maps.InfoWindow();
                infowindow.setContent('<div class="mapCard">' +
                    '<div class="mapCard__title">' +
                    '<a href="#"><?=$taskName;?></a>' +
                    '</div>' +
                    '<div class="mapCard__city"><?=$taskAddress;?></div>' +
                    '<div class="mapCard__start"><span class="color-text">Начать:</span> 3 декабря <span class="color-text">/</span> 15:00</div>' +
                    '<div class="mapCard__cost"><?=$taskPrice;?></div>' +
                    '</div>');
                const marker = new google.maps.Marker({
                    map, position: place,
                    icon: "<?=SITE_TEMPLATE_PATH;?>/images/marker.svg"
                });
                marker.addListener("click", () => {
                    infowindow.open(map, marker);
                });
            }
          </script>
        </div>
      </div>
      <?php } ?>
<script>
$(document).ready(function () {
    $('#offerServices .btn-submit').on('click', function (e) {
        e.preventDefault();
        var offerText = $('#offerServices textarea').val();

        if (offerText.length == 0) {
            return false;
        }

        var taskId = $(this).attr('data-task'),
            taskerId = $(this).attr('data-tasker'),
            taskUri = $('h1').attr('data-uri'),
            customer = $(this).attr('data-customer');

        var jqxhr = $.post(
            "/local/ajax/task/respond-offer.php",
            {
                site: '<?=SITE_ID;?>',
                taskId: taskId,
                taskerId: taskerId,
                offerText: offerText,
                customerId: customer,
                taskName: $('h1').text(),
                taskUri: taskUri
            },
            function () {
                //alert( "success" );
            })
            .done(function (data) {
                var response = jQuery.parseJSON(data);
                console.log(response);
                $('.btn-submit').removeClass('button--loading');
                if (response.result == 'success') {
                    $('.infoBox-setTask').hide();
                    $.magnificPopup.open({
                        items: {
                            src: '#popupOk',
                            type: 'inline'
                        }
                    });
                }
                else if (response.result == 'error') {
                    $.magnificPopup.open({
                        items: {
                            src: '#popupError',
                            type: 'inline',
                        }
                    });
                }
            })
            .fail(function () {
                $('.btn-submit').removeClass('button--loading');
                $.magnificPopup.open({
                    items: {
                        src: '#popupError',
                        type: 'inline'
                    }
                });
            });
    });
});
</script>      
<?php //printr($arResult);
