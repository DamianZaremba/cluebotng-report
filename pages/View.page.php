<?PHP

namespace ReportInterface;

class ViewPage extends Page
{
    private $row;
    private $id;
    private $data;
    private $bad_captca;
    private $bad_comment;

    public function __construct()
    {
        global $mysql;
        $this->id = $_REQUEST['id'];
        $result = mysqli_query($mysql, 'SELECT * FROM `vandalism` WHERE `id` = \'' . mysqli_real_escape_string($mysql, $this->id) . '\'');
        $this->row = mysqli_fetch_assoc($result);
        $this->data = getReport($this->id);
        if ($this->data === null) {
            header('Location: ?page=Report&id=' . $this->id);
            die();
        }

        if (isset($_POST['submit'])) {
            if (trim($_POST['comment']) != '') {
                $this->bad_captca = false;
                if (!isset($_SESSION['username'])) {
                    if (!recaptca_is_valid()) {
                        $this->bad_captca = true;
                    }
                }

                $this->bad_comment = false;
                if (strpos($_POST['comment'], 'http://') !== false) {
                    $this->bad_comment = true;
                }

                if ($this->bad_captca === false && $this->bad_comment === false) {
                    createComment($this->id, $_POST['user'], $_POST['comment']);
                    header('Location: ?page=View&id=' . $this->id);
                    die();
                }
            }
        }

        if (isset($_REQUEST['status']) and isAdmin()) {
            updateStatus($this->id, $_REQUEST['status'], $_SESSION['username']);

            if (isset($_SESSION['next_on_review']) && $_SESSION['next_on_review'] === true) {
                $result = mysqli_query($mysql, "SELECT * FROM `reports` WHERE `status` = 0 ORDER BY RAND() LIMIT 0, 1");
                if (mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    header('Location: ?page=View&id=' . $row['revertid']);
                    die();
                }
            }

            header('Location: ?page=View&id=' . $this->id);
            die();
        }
        if (isset($_REQUEST['deletecomment']) and isSAdmin()) {
            mysqli_query($mysql, 'DELETE FROM `comments` WHERE `commentid` = \'' . mysqli_real_escape_string($mysql, $_REQUEST['deletecomment']) . '\'');
            header('Location: ?page=View&id=' . $this->id);
            die();
        }
    }

    public function writeHeader()
    {
        echo 'Viewing ' . $this->id;
    }

    public function writeContent()
    {
        require 'pages/View.tpl.php';
    }
}

Page::registerPage('View', 'ViewPage', 0, false);
