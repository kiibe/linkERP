<?php
/**
* CalendarView
*
* @version    1.0
* @package    samples
* @subpackage tutor
* @author     Pablo Dall'Oglio
* @copyright  Copyright (c) 2006-2014 Adianti Solutions Ltd. (http://www.adianti.com.br)
* @license    http://www.adianti.com.br/framework-license
*/
class CalendarView extends TPage
{
   private $form;
   private $calendar;
   private $back_action;
   private $next_action;

   /**
    * Class constructor
    * Creates the page
    */
   function __construct()
   {
       parent::__construct();

       // create the calendar
       $this->calendar = new TCalendar;
       // Gett actual data

       $this->calendar->setMonth(date('m'));
       $this->calendar->setYear(date('Y'));

       $this->calendar->selectDays(date("j"));
       $this->calendar->setSize(750,500);

       $this->calendar->setAction( new TAction(array($this, 'onSelect')) );

       // creates a simple form
       $this->form = new TQuickForm('calendar_helper');

       // creates the notebook around the form
       $notebook = new TNotebook(300, 180);
       $notebook->appendPage('Calendar Helper', $this->form);

       // creates the form fields
       $year  = new TEntry('year');
       $month = new TEntry('month');
       $day   = new TEntry('day');
       $year->setValue( $this->calendar->getYear() );
       $month->setValue( $this->calendar->getMonth() );
       $day->setValue( $this->calendar->getSelectedDays() );

       $this->form->addQuickField('Year',  $year,  100);
       $this->form->addQuickField('Month', $month, 100);
       //$this->form->addQuickField('Day',   $day,   100);

       // creates a table to wrap the treeview and the form
       $table = new TTable;
       $this->form->addQuickAction('Back', new TAction(array($this, 'onBack')), 'ico_back.png');
       $this->form->addQuickAction('Next', new TAction(array($this, 'onNext')), 'ico_next.png');
       $row = $table->addRow();
       $cell=$row->addCell($this->calendar)->valign='top';
       $cell=$row->addCell($notebook)->valign='top';

       // wrap the page content using vertical box
       $vbox = new TVBox;
       $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
       $vbox->add($table);
       parent::add($vbox);
   }

   /**
    * Next month
    */
   public function onNext($param)
   {
       $data = $this->form->getData();
       $data->month ++;
       if ($data->month ==13)
       {
           $data->month = 1;
           $data->year ++;
       }
       $this->form->setData( $data );
       $this->calendar->setMonth($data->month);
       $this->calendar->setYear($data->year);
   }

   /**
    * Previous month
    */
   public function onBack($param)
   {
       $data = $this->form->getData();
       $data->month --;
       if ($data->month == 0)
       {
           $data->month = 12;
           $data->year --;
       }
       $this->form->setData( $data );
       $this->calendar->setMonth($data->month);
       $this->calendar->setYear($data->year);
   }

   /**
    * Executed when the user clicks at a tree node
    * @param $param URL parameters containing key and value
    */
   public function onSelect($param)
   {

   }



}
?>
