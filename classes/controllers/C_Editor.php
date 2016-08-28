<?php
require_once('functions/view_helper.php');

class C_Editor extends C_Base{

    protected function before(){
        parent::before();
        $this->menuActive = 'editor';
    }

    function __construct(){
        parent::__construct();
        if (!$this->mUsers->Can('EDITOR_ARTICLES')) {
            $this->redirect('index.php', 1, 'Отказано в доступе.');
        }
    }


    // Консоль редактора
    public function action_index(){
        $this->title_page = 'Консоль редактора';
        $this->title .= '::' . $this->title_page;
        if(isset($_GET['delete'])) {
            if($this->mArticles->delete($_GET['delete']) > 0) {
                $_SESSION['notice'] = 'Статья успешно удаленна';
                $this->redirect('index.php?c=editor&act=index');
            } else {
                $_SESSION['notice'] = 'Ошибка';
            }
        }
        $articles = $this->mArticles->getList();
        $this->content = $this->template('view/templates/v_editor.php', ['articles' => $articles]);
    }

    // Страница создания новой статьи
    public function action_new(){
        $this->title_page = 'Новая статья';
        $this->title .= '::' . $this->title_page;
        if($this->isPost()) {
            if(!empty($_POST) && isset($_POST['title']) && isset($_POST['content'])) {
                if($this->mArticles->checkArticle($_POST['title'], $_POST['content'])) {
                    $this->mArticles->add($_POST['title'], $_POST['content']);
                    $_SESSION['notice'] = 'Статья успешно загружена';
                    $this->redirect('index.php?c=editor&act=index');
                } else {
                    $_SESSION['title'] = $_POST['title'];
                    $_SESSION['content'] = $_POST['content'];
                    $this->redirect('index.php?c=editor&act=new');
                }
            }
        }
        $this->content = $this->template('view/templates/v_new.php');
    }

    // Страница редактирования статьи
    public function action_edit(){
        if(empty($_GET['id'])) {
            $this->redirect('index.php?c=editor&act=index');
        }
        $article = $this->mArticles->getOne($_GET['id']);
        $id = $_GET['id'];
        if(!empty($_POST) && isset($_POST['title']) && isset($_POST['content'])) {
            $title_new = $_POST['title'];
            $content_new = $_POST['content'];
            if($this->mArticles->checkArticle($title_new, $content_new)) {
                $this->mArticles->update($id, $title_new, $content_new);
                $_SESSION['notice'] = 'Статья успешно отредактирована';
                $this->redirect('index.php?c=editor&act=index');
            } else {
                $this->redirect("index.php?c=editor&act=edit&id=$id");
            }
        }
        $this->title_page = 'Редактирование статьи';
        $this->title .= '::' . $this->title_page;
        $this->content = $this->template('view/templates/v_edit.php', ['article' => $article]);
    }

}