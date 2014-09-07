<?
require "../../../lib.php";
require_once "{$root}/lib/Twig/Autoloader.php";


function parseStaffModulesCSV($data) {
	$lines = explode("\n",$data);
	$staffmodules = array();
	foreach($lines as $line) {
		$csv = str_getcsv($line);
		if (count($csv) < 2)
			continue;
		
		$staffmodules[] = array(
			"ModuleID" => strtoupper($csv[0]),
			"Semester" => $csv[1],
			"Staff" => array_map('strtolower',array_slice($csv, 2))
		);
	}
	if ($staffmodules[0]["ModuleID"] == "MODULES") {
		array_shift($staffmodules);
	}
	return $staffmodules;
}

function insertStaffModules($staffmodules, $questionnaireID) {
	global $db;
	$dbsmodule = new tidy_sql($db, "REPLACE INTO StaffToModules (ModuleID, UserID, QuestionaireID) VALUES (?, ?, ?)", "ssi");
	foreach($staffmodules as $module) {
		foreach($module["Staff"] as $staff) {
			$dbsmodule->query($module["ModuleID"], $staff, $questionnaireID);
		}
	}
}


Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem("{$root}/admin/tpl/");
$twig = new Twig_Environment($loader, array());

$template = $twig->loadTemplate('questionnaire/import/staffmodules.html');

$questionnaireID = $_GET["questionnaireID"];
$alerts = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$data = parseStaffModulesCSV($_POST["csvdata"]);
	insertStaffModules($data, $questionnaireID);
	$alerts[] = array("type"=>"success", "message"=>"Staff modules inserted");
}

function getStaffModules($questionnaireID) {
	global $db;
	$stmt = new tidy_sql($db, "SELECT * FROM StaffToModules WHERE QuestionaireID=?", "i");
	return $stmt->query($questionnaireID);
}

$staffToModules = getStaffModules($questionnaireID);

echo $template->render(array(
	"staffToModules"=>$staffToModules,"url"=>$url, "questionnaireID"=> $questionnaireID, "alerts"=>$alerts
));

