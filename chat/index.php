<?define('NEED_AUTH', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$APPLICATION->SetTitle('Chat room');
?>
<?$APPLICATION->IncludeComponent(
	"task:chat", 
	".default", 
	array(
		"CACHE_TYPE" => "N",
		"COMPONENT_TEMPLATE" => ".default",
		"MESS_COUNT" => "20", // количество сообщений в окне
		"MESS_EXT" => "", // расширения файлов чата
		"MESS_FILESIZE" => "20", // размер фалов в МБ
	),
	false
);?>
<?require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';?>