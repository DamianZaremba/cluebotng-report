<?PHP

namespace ReportInterface;

class MainPage extends Page
{
    public function __construct()
    {
    }

    public function writeHeader()
    {
        echo 'Report';
    }

    public function writeContent()
    {
        echo '<form action="?page=Report" method="post">';

        echo '<h3>You need to get ClueBot NG\'s revert ID, there are two ways to do this:</h3>';

        echo '<b>From the article:</b><br />';
        echo '<div class="image" style="width: 600px">';
        echo '<img alt="" src="//upload.wikimedia.org/wikipedia/commons/thumb/9/9a/ClueBot_False_Positive_Method_1_Step_1.png/799px-ClueBot_False_Positive_Method_1_Step_1.png" width="600" height="63" /><br />';
        echo '<small>Click the history tab at the top of the article.</small>';
        echo '</div>';
        echo '<div class="image" style="width: 600px">';
        echo '<img alt="" src="//upload.wikimedia.org/wikipedia/commons/thumb/1/10/ClueBot_False_Positive_Method_1_Step_2.png/800px-ClueBot_False_Positive_Method_1_Step_2.png" width="600" height="180" class="thumbimage" /><br />';
        echo '<small>The revert ID is in parenthesis right after "Thanks, ClueBot NG".</small>';
        echo '</div>';

        echo '<b>From your talk page:</b><br />';
        echo '<div class="image" style="width: 600px">';
        echo '<img alt="" src="//upload.wikimedia.org/wikipedia/commons/thumb/6/60/ClueBot_False_Positive_Method_2_Step_1.png/800px-ClueBot_False_Positive_Method_2_Step_1.png" width="600" height="60" class="thumbimage" /><br />';
        echo '<small>Click the "edit this page" tab at the top of the page.</small>';
        echo '</div>';
        echo '<div class="image" style="width: 600px">';
        echo '<img alt="" src="//upload.wikimedia.org/wikipedia/commons/thumb/2/20/ClueBot_False_Positive_Method_2_Step_2.png/800px-ClueBot_False_Positive_Method_2_Step_2.png" width="600" height="241" class="thumbimage" /><br />';
        echo '<small>The revert ID is in the text right after "MySQL ID:".</small>';
        echo '</div>';

        echo 'Revert ID: ';
        echo '<input type="text" name="id">';
        echo '<input type="submit" value="Submit">';

        echo '</form>';
    }
}

Page::registerPage('Main', 'MainPage', 0);
