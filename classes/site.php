<?php

class site
{
    private $headers;
    private $footers;
    private $page;
    private $title;
    private $errors;
    private $success;

    public function __construct($pageTitle, $errors = null, $success = false)
    {
        $this->headers = array();
        $this->footers = array();
        $this->title = $pageTitle;
        $this->errors = $errors;
        $this->success = $success;
    }

    public function render()
    {
        foreach ($this->headers as $header) {
            include $header;
        }

        echo '<title>' . $this->title . '</title>';

        $this->renderErrors();
        $this->renderSuccess();

        $this->page->render();

        foreach ($this->footers as $footer) {
            include $footer;
        }
    }

    public function addHeader($file)
    {
        $this->headers[] = $file;
    }

    public function addFooter($file)
    {
        $this->footers[] = $file;
    }

    public function setPage(page $page)
    {
        $this->page = $page;
    }

    private function renderErrors()
    {
        foreach ($this->errors as $error) {
            echo '<div class="alert alert-danger" role="alert">';
            echo $error;
            echo '</div>';
        }
    }

    private function renderSuccess()
    {
        if ($this->success) {
            echo '<div class="alert alert-success" role="alert">Success!</div>';
        }
    }
}
