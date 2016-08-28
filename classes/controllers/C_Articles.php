<?php
require_once('functions/view_helper.php');

class C_Articles extends C_Base{

    protected function before(){
        parent::before();
        $this->menuActive = 'article';
    }

	// Главная страница
	public function action_index(){
        $this->title_page = 'Главная';
        $this->title .= '::' . $this->title_page;
        if($_SESSION['num'] === null) {
            $_SESSION['num'] = 5;
        }
        if(isset($_GET['num'])) {
            $valid_a = [3, 5, 10];
            if($this->validateParam($_GET['num'], $valid_a)) {
                $_SESSION['num'] = $_GET['num'];
            }
            $this->redirect($_SERVER['PHP_SELF']);
        }
        $this->mUsers->ClearSessions();
		$count = $this->mArticles->count();
		$n = $count / $_SESSION['num'];
        if(isset($_GET['page'])) {
            $valid_a = range(1, ceil($n));
            if(!$this->validateParam($_GET['page'], $valid_a)) {
                $this->redirect($_SERVER['PHP_SELF']);
            }
        }
        $usersOnline = $this->mUsers->isOnline();
        $articles = $this->mArticles->getIntro(40, $_GET['page'], $_SESSION['num']);
        $sort = $this->template('view/templates/block/v_block_sort.php');
        $nav = $this->template('view/templates/block/v_block_nav.php', ['n' => $n]);
        $array = ['articles' => $articles, 'nav' => $nav, 'sort' => $sort, 'usersOnline' => $usersOnline];
		$this->content = $this->template('view/templates/v_index.php', $array);
	}

	// Страница просмотра одной статьи
	public function action_article(){
        if($this->isGet()) {
            $article = $this->mArticles->getOne($_GET['id']);
            $comments = $this->mArticles->getComments($_GET['id']);
        }
        if($this->isPost()) {
            if(isset($_SESSION['name']) && isset($_POST['comment']) && isset($_GET['id'])) {
                if($this->mArticles->checkComment($_POST['comment'])) {
                    $this->mArticles->addComment($_GET['id'], $_SESSION['name'], $_POST['comment']);
                    $_SESSION['notice'] = 'Комментарий успешно добавлен';
                    $this->redirect($_SERVER['REQUEST_URI']);
                } else {
                    $_SESSION['comment'] = $_POST['comment'];
                    $this->redirect($_SERVER['REQUEST_URI']);
                }
            }
        }
        $this->title_page = $article['title'];
        $this->title .= '::' . $this->title_page;
        $comment_form = $this->template('view/templates/block/v_block_comment_form.php');
        if (!$this->mUsers->Can('ADD_COMMENTS')) {
            $comment_form = '';
        }
        $array = ['article' => $article, 'comments' => $comments, 'comment_form' => $comment_form];
		$this->content = $this->template('view/templates/v_article.php', $array);
	}

}