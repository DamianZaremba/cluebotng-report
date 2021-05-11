<?PHP

namespace ReportInterface;

class SignInPage extends Page
{
    public function __construct()
    {
        global $mysql;
        if (isset($_POST['submit'])) {
            $query = 'SELECT `userid`, `username`, `admin`, `superadmin`, `next_on_review`, `email` FROM `users` WHERE `username` = ';
            $query .= '\'' . mysqli_real_escape_string($mysql, $_POST['username']) . '\' AND `password` = ';
            $query .= 'PASSWORD(\'' . mysqli_real_escape_string($mysql, $_POST['password']) . '\')';
            $row = mysqli_fetch_assoc(mysqli_query($mysql, $query));
            if ($row) {
                $_SESSION['userid'] = $row['userid'];
                $_SESSION['next_on_review'] = $row['next_on_review'] ? true : false;
                $_SESSION['email'] = $row['email'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['admin'] = $row['admin'] ? true : false;
                $_SESSION['sadmin'] = $row['superadmin'] ? true : false;

                header('Location: ?page=List');
                die();
            } else {
                header('Location: ?page=Sign+In');
                die();
            }
        }
    }

    public function writeHeader()
    {
        echo 'Sign In';
    }

    public function writeContent()
    {
        echo '<form method="post">';
        echo '<table>';
        echo '<tr><th>Username:</th><td><input type="text" name="username" /></td></tr>';
        echo '<tr><th>Password:</th><td><input type="password" name="password" /></td></tr>';
        echo '<tr><td colspan=2><input type="submit" name="submit" value="Sign In" /></td></tr>';
        echo '</table>';
        echo '</form>';
    }
}

if (!isset($_SESSION['username'])) {
    Page::registerPage('Sign In', 'SignInPage', 3);
}
