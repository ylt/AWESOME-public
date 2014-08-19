<?
require "../../../lib.php";
require_once "{$root}/lib/Twig/Autoloader.php";

function getQuestionaire($questionaireID) {
	global $db;

	$stmt = new tidy_sql($db, "
		SELECT * FROM Questionaires WHERE QuestionaireID=?", "i");

	$rows = $stmt->query($questionaireID);
	
	return $rows[0];
}

function updateQuestionaire($questionaireID, $fields) {
	global $db;

	$stmt = new tidy_sql($db, "
		UPDATE Questionaires SET QuestionaireName=?, QuestionaireDepartment=? WHERE QuestionaireID=?", "ssi");

	$stmt->query($fields["QuestionaireName"], $fields["QuestionaireDepartment"], $questionaireID);
}

Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem("{$root}/admin/tpl/");
$twig = new Twig_Environment($loader, array());

$template = $twig->loadTemplate('import-basic.html');

$questionaireID = $_GET["questionaireID"];
$alerts = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$q = array();
		$q["QuestionaireName"] = $_POST["questionaireName"];
		$q["QuestionaireDepartment"] = $_POST["questionaireDepartment"];
		
		updateQuestionaire($questionaireID, $q);
		
		$alerts[] = array("type"=>"success", "message"=>"Questionnaire modified");
}

$q = getQuestionaire($questionaireID);

echo $template->render(array(
	"url"=>$url, "questionaireID"=> $questionaireID, "alerts"=>$alerts,
	"questionaire"=>array("name"=>$q["QuestionaireName"], "department"=>$q["QuestionaireDepartment"])
));


