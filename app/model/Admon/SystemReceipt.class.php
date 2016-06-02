<?php
/**
 * System_group Active Record
 * @author  <your-name-here>
 */
class SystemReceipt extends TRecord
{
    const TABLENAME = 'system_receipt';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}


    private $system_programs = array();

    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('client');
        parent::addAttribute('account_money');
        parent::addAttribute('date');
    }

    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        // delete the related System_userSystem_user_group objects
        $id = isset($id) ? $id : $this->id;
        $repository = new TRepository('SystemReceipt');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('system_user_id', '=', $id));
        $repository->delete($criteria);

        // delete the object itself
        parent::delete($id);
    }

}
