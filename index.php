<?php
require_once __DIR__ . '/crest/settings.php';
require_once __DIR__ . '/controllers/SpaController.php';
require_once __DIR__ . '/utils/index.php';
require_once(__DIR__ . '/crest/crestcurrent.php');

include_once __DIR__ . '/views/header.php';


$result = CRestCurrent::call('user.current');
$currentUser = $result['result'];
$currentUserId = $currentUser['ID'];
$_SESSION['currentUserId'] = $currentUserId;
$isAdmin = isAdmin($currentUserId);

include 'views/components/toast.php';
include 'views/components/topbar.php';

$pages = [
    'properties' => 'views/properties/index.php',
    'add-property' => 'views/properties/add.php',
    'edit-property' => 'views/properties/edit.php',
    'view-property' => 'views/properties/view.php',

    'pocket' => 'views/pocket/index.php',
    'add-pocket' => 'views/pocket/add.php',
    'edit-pocket' => 'views/pocket/edit.php',
    'view-pocket' => 'views/pocket/view.php',

    'agents' => 'views/agents/index.php',
    'developers' => 'views/developers/index.php',
    'pf-locations' => 'views/pf-locations/index.php',
    'bayut-locations' => 'views/bayut-locations/index.php',
    'settings' => 'views/settings/index.php',
    'reports' => 'views/reports/index.php',
];

$page = isset($_GET['page']) && array_key_exists($_GET['page'], $pages) ? $_GET['page'] : 'properties';

require_once $pages[$page];

if (!array_key_exists($page, $pages)) {
    header("Location: index.php?page=properties';");
    exit;
}

include_once __DIR__ . '/views/footer.php';
?>
<script>
    localStorage.setItem('currentUserId', '<?php echo $currentUserId; ?>');
</script>