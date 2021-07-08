<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use Bitrix\Sale,
    Bitrix\Main\Loader,
    Bitrix\Main\Entity,
    Bitrix\Main\Application;

class PersonalChat extends CBitrixComponent
{
    private function checkModules()
    {
        if (!Loader::includeModule('iblock') && !Loader::includeModule('pull') && !Loader::includeModule('highloadblock')) {
            throw new \Exception('Не загружен модуль iblock / pull / highloadblock');
        }
        return true;
    }
    // проверка для поиска по словам в чате и названии задачи
    public function Search()
    {
        $word = false;
        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
        $word = $request->get("word");
        return $word;
    }
    // отправка сообщения в очередь
    public function SendPull($parnter_id, $id_from)
    {
        CPullWatch::AddToStack('PULL_TEST',
            Array(
                'module_id' => 'test',
                'command' => 'check',
                'params' => Array("ID_TO" => $parnter_id, "ID_FROM" => $id_from)
            )
        );
        $result = 'успешно добавлен';
        return $result;
    }
    // мой ИД
    public function GetMyID()
    {
        global $USER;
        $user_id = $USER->GetID();
        return $user_id;
    }
	// для хайлоадов
    public function GetEntityDataClass($hlblock_id)
    {
        $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlblock_id)->fetch();
        $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();
        return $entity_data_class;
    }
	// проверка на свойство заказчика
    public function MaybeCustomer($messages)
    {
        $my_id = $this->GetMyID();
        $dbUser = \Bitrix\Main\UserTable::getList(array(
            'select' => array('UF_ACCOUNT'),
            'filter' => array('ID' => $my_id)
        ));
        if ($arUser = $dbUser->fetch())
        {   // если я и есть заказчик 
            if($arUser['UF_ACCOUNT'] == 1)
            {
                // проходка по моим заданиям 
                $dbItem = \Bitrix\Iblock\ElementTable::getList(array(
                    'select' => array('ID', 'IBLOCK_ID'), 
                    'filter' => array('IBLOCK_ID' => 2, 'CREATED_BY' => $my_id, 'ACTIVE' => 'Y'),
                    'order' => array('ID' => 'ASC')
                ));
                while ($arItem = $dbItem->fetch()) 
                {
                    $dbProperty = \CIBlockElement::getProperty($arItem['IBLOCK_ID'], $arItem['ID'], array("sort", "asc"), array('CODE' => 'TASK_STATUS'));
                    if ($arProperty = $dbProperty->GetNext()) 
                    {   // если оно активно и не завершено
                        if ($arProperty['VALUE'] != 'Выполнено') 
                        {   // проходка по ИБ оферов с ид задания
                            $entity_data_class = $this->GetEntityDataClass(1);
                            $rsData = $entity_data_class::getList(
                                array(
                                    "select" => array("*"),
                                    "order" => array("ID" => "ASC"),
                                    "filter" => array("UF_TASK_ID" => $arItem['ID']),
                                )
                            );
                            while($arData = $rsData->Fetch())
                            {
                                // добавление к списку сообщений и чатеров - авторов предложений 
                                $messages[] = [
                                    'UF_DATE' => $arData['UF_DATA_START'],
                                    'UF_FROM' => $arData['UF_USER_ID'],
                                    'UF_TO'   => $my_id,
                                    'UF_TEXT' => $arData['UF_OFFER_TEXT'],
                                    'UF_WORK' => $arItem['ID'],
                                    'MY' => '',
                                ];
                            }
                        }
                    }
                }
            }
            // если исполнитель
            else
            {
                $entity_data_class = $this->GetEntityDataClass(1);
                $rsData = $entity_data_class::getList(
                    array(
                        "select" => array("*"),
                        "order" => array("ID" => "ASC"),
                        "filter" => array("UF_USER_ID" => $my_id),
                    )
                );
                while($arData = $rsData->Fetch())
                {
                    $dbItem = \Bitrix\Iblock\ElementTable::getList(array(
                        'select' => array('CREATED_BY'), 
                        'filter' => array('ID' => $arData['UF_TASK_ID']),
                    ));
                    if ($arItem = $dbItem->fetch()) 
                    {
                        $UF_TO = $arItem['CREATED_BY'];
                    }
                    // добавление к списку сообщений и чатеров - авторов предложений 
                    $messages[] = [
                        'UF_DATE' => $arData['UF_DATA_START'],
                        'UF_FROM' => $my_id,
                        'UF_TO'   => $UF_TO,
                        'UF_TEXT' => $arData['UF_OFFER_TEXT'],
                        'UF_WORK' => $arData['UF_TASK_ID'],
                        'MY' 	  => 'Y',
						'OFFER'   => 'Y',
                    ];
                }
            }
        }
        return $messages;
    }
    // проверка диалога на тип "заказчик"
    public function NotActualType($ID)
    {
        $result = false;
        $my_id = $this->GetMyID();

        $dbUser = \Bitrix\Main\UserTable::getList(array(
            'select' => array('UF_ACCOUNT'),
            'filter' => array('ID' => $my_id)
        ));
        if ($arUser = $dbUser->fetch())
        {   
            $dbItem = \Bitrix\Iblock\ElementTable::getList(array(
                'select' => array('CREATED_BY'), 
                'filter' => array('ID' => $ID),
            ));
            if ($arItem = $dbItem->fetch()) 
            {
                // если я заказчик - не выводим сообщения, где я исполниетль и наоборот
                if($arUser['UF_ACCOUNT'] == 1 && $my_id != $arItem['CREATED_BY'] || $arUser['UF_ACCOUNT'] != 1 && $my_id == $arItem['CREATED_BY'])
                    $result = true;
                
            }
        }
        return $result;
    }

    // сбор всех сообщений
    public function getAllMessage()
    {
        $user_id = $this->GetMyID();
        $messages = [];
        $messages = $this->MaybeCustomer($messages);
        $entity_data_class = $this->GetEntityDataClass(3);
        $rsData = $entity_data_class::getList(array(
            "select" => array("*"),
            "order" => array("ID" => "ASC"),
            "filter" => Array(
                Array(
                    "LOGIC"=>"OR",
                    Array(
                        "UF_FROM" => $user_id
                    ),
                    Array(
                        "UF_TO" => $user_id
                    ),
                ),
            ),
        ));
        while($arData = $rsData->Fetch())
        {
            $arData['MY'] = ($arData['UF_FROM'] == $user_id) ? 'Y' : '';
            // проверка меня как заказчика по таскам, если я исполнитель
            if($this->NotActualType($arData['UF_WORK'])) continue; 
            $messages[] = $arData;
        }
        return $messages;
    }

    // ИД оппонента
    public function GetPartnerID($messages)
    {
        foreach($messages as $message)
        {
            $partner_id = (!empty($message['MY'])) ? $message['UF_TO'] : $message['UF_FROM'];
            break;
        }
        return $partner_id;
    }
    // сортировка списка чаттеров
    public function SortChatList($chat_list)
    {
        $new_chatlist = [];
        foreach($chat_list as $key => $value)
        {
            $new_chatlist[ strtotime($value['DATE']) ] = $value;
        }
        krsort($new_chatlist);
        return $new_chatlist;
    }
    // генерация списка чатеров
    public function MakeChatList($messages)
    {
        $ad_list = []; $list = [];
        foreach($messages as $message)
        {
			$chatter = (!empty($message['MY'])) ? $message['UF_TO'] : $message['UF_FROM'];
            $ad_list[ $message['UF_WORK'].'_'.$chatter ][] = $message;
        }

        $w = $this->Search();
        foreach($ad_list as $ad_id => $message)
        {
            $iden = explode("_", $ad_id); 
            $ad_id = $iden[0];
            $chatter = $iden[1];
            
            $dbItem = \Bitrix\Iblock\ElementTable::getList(array(
                'select' => array('IBLOCK_ID', 'ID', 'NAME', 'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'CREATED_BY'),
                'filter' => array('ID' => $ad_id),
            ));
            if ($arItem = $dbItem->fetch()) 
            {
                $dbProperty = \CIBlockElement::getProperty($arItem['IBLOCK_ID'], $arItem['ID'], array("sort", "asc"), array());
                while ($arProperty = $dbProperty->GetNext()) 
                {
                    if($arProperty['CODE'] == 'TASK_STATUS') $status = $arProperty['VALUE'];
                    if($arProperty['CODE'] == 'TASK_'.mb_strtoupper(LANGUAGE_ID).'_TITLE') $arItem['NAME'] = $arProperty['VALUE'];
                    if($arProperty['CODE'] == 'TASK_PERFORMER') $performer = $arProperty['VALUE']; 
                }

                $partner_id = $chatter;
                if(empty($partner_id)) $partner_id = $arItem['CREATED_BY'];
                $user = \Bitrix\Main\UserTable::getById($partner_id)->fetch();
                $name = $user['NAME'].' '.$user['SECOND_NAME'].' '.$user['LAST_NAME'];
                if(!empty($arItem['PREVIEW_PICTURE']) || !empty($arItem['DETAIL_PICTURE']))
                {
                    $pic = ($arItem['PREVIEW_PICTURE'] > 0) ? CFile::GetPath($arItem['PREVIEW_PICTURE']) : CFile::GetPath($arItem['DETAIL_PICTURE']);
                } 
                else
                $pic = ''; //'/bitrix/components/bitrix/catalog.section/templates/.default/images/no_photo.png';

                $online = CUser::IsOnLine($partner_id, $interval=120);
                if($w)
                {
                    $check = 'N';
                    foreach($message as $mess)
                    {
                        if (strpos($mess['UF_TEXT'],$w) !== false) $check = 'Y';
                    }
                    if (strpos($arItem['NAME'],$w) !== false) $check = 'Y';
                    if($check == 'N') continue;
                }
                $last_value = end($message);
                $list[] = [
                    'NAME' => $arItem['NAME'],
                    'ID' => $arItem['ID'],
                    'PICTURE' => $pic,
                    'AUTHOR' => $name,
                    'STATUS' => $status,
                    'PERFORMER' => $performer,
                    'ONLINE' => $online,
                    'TEXT' => $last_value['UF_TEXT'],
                    'DATE' => $last_value['UF_DATE'],
                    'PARTNER_ID' => $partner_id,
                ];
            }
        }
        $list = $this->SortChatList($list);
        return $list;
    }
    // сортировка сообщений для среднего окна
    public function GetActiveMessages($id, $messages, $parnter_id)
    {
        if(!empty($id) && is_numeric($id) && $id > 0)
        {
            $active_messages = [];
            foreach($messages as $message)
            {
                if($message["UF_WORK"] == $id && ($message["UF_FROM"] == $parnter_id || $message["UF_TO"] == $parnter_id)) 
                    $active_messages[] = $message;
            }
        }
        // ограничение по количеству сообщений
        $count = $this->arParams['MESS_COUNT'];
        if(!empty($count) && is_numeric($element))
        {
            if(count($active_messages) > $count)
            {
                $diff = count($active_messages) - $count;
                foreach ($active_messages as $key => $value) 
                {
                    if(($key+1) <= $diff) 
                        unset($active_messages[$key]);
                }
            }
        }
        return $active_messages;
    }
    
    // проверка на выбранный чат в адресной строке id="123"
    public function CheckChanges()
    {
        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
        $id = $request->get("id");
        $add = $request->get("add");
        $parnter_id = $request->get("parnter_id");
        if (strpos($id,'_') !== false)
        {
            $iden = explode("_", $id); 
            $id = $iden[0];
            $parnter_id = $iden[1];
        }
        $chat_id = false;
        if(!empty($id) && is_numeric($id) && $id > 0)
        {
            $chat_id = $id;
        }
        
        if(!empty($add))
        {
            $this->addMessage($parnter_id);
        }
        $res['CHAT_ID'] = $chat_id;
        $res['CHATTER'] = $parnter_id;
        return $res;
    }
    // инфа открытого чата
    public function GetActiveUser($id,$user_id)
    {
        if(!empty($id) && is_numeric($id) && $id > 0)
        {
            $dbItem = \Bitrix\Iblock\ElementTable::getList(array(
                'select' => array('IBLOCK_ID', 'ID', 'CREATED_BY', 'DETAIL_PICTURE', 'DATE_CREATE', 'NAME'),
                'filter' => array('ID' => $id),
            ));
            if ($arItem = $dbItem->fetch()) 
            {
                $dbProperty = \CIBlockElement::getProperty($arItem['IBLOCK_ID'], $arItem['ID'], array("sort", "asc"), array());
                while ($arProperty = $dbProperty->GetNext())
                {
                    if($arProperty['CODE'] == 'TASK_STATUS') $status = $arProperty['VALUE'];
                    if($arProperty['CODE'] == 'TASK_PRICE') $price = $arProperty['VALUE'];
                    if($arProperty['CODE'] == 'TASK_ADDRESS') $place = $arProperty['VALUE'];
                    if($arProperty['CODE'] == 'TASK_START_TIME') $start = $arProperty['VALUE'];
                    if($arProperty['CODE'] == 'TASK_'.mb_strtoupper(LANGUAGE_ID).'_TITLE' && !empty($arProperty['VALUE'])) $arItem['NAME'] = $arProperty['VALUE'];
                    if($arProperty['CODE'] == 'TASK_PERFORMER') $performer = $arProperty['VALUE']; 
                }
                if(empty($user_id)) $user_id = $arItem['CREATED_BY'];
                $user = \Bitrix\Main\UserTable::getList(array(
                    'filter' => array('=ID'=>$user_id),
                    'select' => array('*','UF_*'),
                ))->fetch();
                $pic = (!empty($user['PERSONAL_PHOTO'])) ? CFile::GetPath($user['PERSONAL_PHOTO']) : '/local/templates/taskme/images/icom.png';
                $picture = (!empty($arItem['DETAIL_PICTURE'])) ? CFile::GetPath($arItem['DETAIL_PICTURE']) : ''; //'/bitrix/components/bitrix/catalog.section/templates/.default/images/no_photo.png';

                if (Loader::includeModule('sale'))
                {
                    $loc = \Bitrix\Sale\Location\LocationTable::getList(array(
                        'filter' => array(
                            '=ID' => $user['PERSONAL_COUNTRY'], 
                            '=NAME.LANGUAGE_ID' => LANGUAGE_ID
                        ),
                        'select' => array(
                            'NAME_RU' => 'NAME.NAME',
                        )
                    ))->fetch();
                    $country = $loc['NAME_RU'];
                }
                $online = CUser::IsOnLine($user_id, 120);

                $user_info = [
                    'NAME' => $user['NAME'],
                    'LAST_NAME' => $user['LAST_NAME'],
                    'PICTURE' => $pic,
                    'COUNTRY' => $country,
                    'CITY' => $user['PERSONAL_CITY'],
                    'ONLINE' => $online,
                    'PHONE' => $user['PERSONAL_PHONE'],
                    'RATE' => $user['UF_RATING_CUSTOMER'],
                    'REVIEWS' => $user['UF_CUSTOMER_REVIEW'],
                    'LAST_DATE' => $user['LAST_ACTIVITY_DATE'],
                ];

                $task_info = [
                    'NAME' => $arItem['NAME'],
                    'PICTURE' => $picture,
                    'PRICE' => $price,
                    'STATUS' => $status,
                    'PERFORMER' => $performer,
                    'PLACE' => $place,
                    'START' => $start,
                    'CREATE' => $arItem['DATE_CREATE'],
                ];
            }
            $info = [
                'USER' => $user_info,
                'TASK' => $task_info,
            ];
        }
        return $info;
    }
	// новое сообщение
    public function addMessage($parnter_id)
    {
        $id_from = $this->GetMyID();
        $hlblock_id = 3;
        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
        $ad_id = $request->get("id");
        $text = $request->get("text");
        $file = $request->getFile("file");
        $fid = CFile::SaveFile($_FILES["file"], 'chat/'.$ad_id);
        if(empty($parnter_id))
        {
            $dbItem = \Bitrix\Iblock\ElementTable::getList(array(
                'select' => array('CREATED_BY'),
                'filter' => array('ID' => $ad_id),
            ));
            if ($arItem = $dbItem->fetch()) $parnter_id = $arItem['CREATED_BY'];
        }

        $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlblock_id)->fetch();
        $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock); 
        $entity_data_class = $entity->getDataClass(); 
        $date = new DateTime();
        $UF_DATE = $date->format('d.m.Y H:i:sP');
        //Массив добавляемых параметров
        $data = array(
            "UF_DATE" => $UF_DATE,
            "UF_FROM" => $id_from,
            "UF_TO" => $parnter_id,
            "UF_TEXT" => $text,
            "UF_WORK" => $ad_id,
            "UF_FILE" => \CFile::MakeFileArray($fid),
        );
        $res = $entity_data_class::add($data);
        $result = $this->SendPull($parnter_id, $id_from);
        
        return $result;
    }

    // формирование шаблона
    public function onPrepareComponentParams($arParams)
    {
		$filesize = (is_numeric($arParams["MESS_FILESIZE"])) ? $arParams["MESS_FILESIZE"] : 1;
        $result = array(
            "CACHE_TYPE" => $arParams["CACHE_TYPE"],
            "CACHE_TIME" => isset($arParams["CACHE_TIME"]) ? $arParams["CACHE_TIME"]: 36000000,
			"FILESIZE" => $filesize*1000000,
			"EXT" => $arParams['MESS_EXT'],
        );
        return $result;
    }

    public function executeComponent()
    {
        if($this->startResultCache())
        {
            $result = [];
            // проверка модулей
            $this->checkModules();
            // формирование списка
            $mess = $this->getAllMessage();
            $result['CHAT_LIST'] = $this->MakeChatList($mess);
            $res = $this->CheckChanges();
            $chat_id = $res['CHAT_ID'];
            $parnter_id = $res['CHATTER'];
            if($chat_id)
            {
                $result['CHAT_ACTIVE'] = $this->GetActiveMessages($chat_id, $mess, $parnter_id);
                $user_id = $this->GetPartnerID($result['CHAT_ACTIVE']);
                $result['CHAT_INFO'] = $this->GetActiveUser($chat_id,$user_id);
            }

            $this->arResult = $result;
            $this->includeComponentTemplate();
        }
        return $this->arResult;
    }
}
?>