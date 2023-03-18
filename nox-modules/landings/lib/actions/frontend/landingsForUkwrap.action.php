<?

class landingsForUkwrapAction extends noxThemeAction {

    public function execute() {
        $this->title = 'Vector car templates for branding agencies in UK';
        $this->addCss('/nox-themes/lp/wrap-uk/css/style.css');

        $vectorModel = new printsVectorModel();
        $this->addVar('l1', [233,102,198,88]);
        $this->addVar('l2', [104,99,140,39]);
        $this->addVar('vectors', $vectorModel->where('id', [233,102,198,88,104,99,140,39])->fetchAll('id'));

        setcookie('_um', 'bzu', time() + 31536000, '/');
        addWindows('registration-lp-wrap-uk');
    }
}