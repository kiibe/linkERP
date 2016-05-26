<?php
/**
 * LoginForm Registration
 * @author  <your name here>
 */
class LoginForm extends TPage
{
    protected $form; // form
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct($param)
    {
        parent::__construct();

       /* $container3 = new TElement('div');
        $companyName= '<span style="font-size:30px"/>'
        $companyName -> ddCell( new TLabel('Nombre de la empresa') )
        $container3 -> add($companyName)*/

       /* $table2 = new TTable;
        $table2 -> width = '1000px';
        $table2 -> height = '100px';

        $row2=$table2->addRow();
        $row2->addCell( new TLabel('Nombre empresa') )-> colspan = 2;
        //$row2->class='tformtitle';

        $container1 = new TElement('div');
        $container1->add($user);*/


        $table = new TTable;
        $table->width = '100%';
        // creates the form
        $this->form = new TForm('form_login');
        $this->form->class = 'tform';
        $this->form->style = 'max-width: 400px; margin:auto; margin-top:120px;';

        // add the notebook inside the form
        $this->form->add($table);

        // create the form fields
        $login = new TEntry('login');
        $password = new TPassword('password');
        
        // define the sizes
        $login->setSize('70%', 40);
        $password->setSize('70%', 40);

        $login->style = 'height:35px; font-size:14px;float:left;border-bottom-left-radius: 0;border-top-left-radius: 0;';
        $password->style = 'height:35px;margin-bottom: 15px;font-size:14px;float:left;border-bottom-left-radius: 0;border-top-left-radius: 0;';

        $row=$table->addRow();
        $row->addCell( new TLabel('Log in') )->colspan = 2;
        $row->class='tformtitle';

        $login->placeholder = _t('User');
        $password->placeholder = _t('Password');

        $user = '<span style="float:left;width:35px;margin-left:45px;height:35px;" class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span>';
        $locker = '<span style="float:left;width:35px;margin-left:45px;height:35px;" class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>';

        $container1 = new TElement('div');
        $container1->add($user);
        $container1->add($login);

        $container2 = new TElement('div');
        $container2->add($locker);
        $container2->add($password);

        $row=$table->addRow();
        $row->addCell($container1)->colspan = 2;

        // add a row for the field password
        $row=$table->addRow();        
        $row->addCell($container2)->colspan = 2;
        
        // create an action button (save)
        $save_button=new TButton('save');
        // define the button action
        $save_button->setAction(new TAction(array($this, 'onLogin')), _t('Log in'));
        $save_button->class = 'btn btn-success';
        $save_button->style = 'font-size:18px;width:70%;padding:10px';

        $row=$table->addRow();
        $row->class = 'tformaction';
        $cell = $row->addCell( $save_button );
        $cell->colspan = 2;
        $cell->style = 'text-align:center';

        $this->form->setFields(array($login, $password, $save_button));

        // add the form to the page
        //parent::add($table2);
        parent::add($this->form);

    }

    /**
     * Autenticates the User
     */
    function onLogin()
    {
        try
        {
            TTransaction::open('permission');
            $data = $this->form->getData('StdClass');
            $this->form->validate();
            $user = SystemUser::autenticate( $data->login, $data->password );
            if ($user)
            {
                TSession::regenerate();
                $programs = $user->getPrograms();
                $programs['LoginForm'] = TRUE;
                
                TSession::setValue('logged', TRUE);
                TSession::setValue('login', $data->login);
                TSession::setValue('username', $user->name);
                TSession::setValue('frontpage', '');
                TSession::setValue('programs',$programs);
                
                $frontpage = $user->frontpage;
                SystemAccessLog::registerLogin();
                if ($frontpage instanceof SystemProgram AND $frontpage->controller)
                {
                    TApplication::gotoPage($frontpage->controller); // reload
                    TSession::setValue('frontpage', $frontpage->controller);
                }
                else
                {
                    TApplication::gotoPage('EmptyPage'); // reload
                    TSession::setValue('frontpage', 'EmptyPage');
                }
            }
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error',$e->getMessage());
            TSession::setValue('logged', FALSE);
            TTransaction::rollback();
        }
    }
    
    /**
     * Logout
     */
    public static function onLogout()
    {
        SystemAccessLog::registerLogout();
        TSession::freeSession();
        TApplication::gotoPage('LoginForm', '');
    }
}
