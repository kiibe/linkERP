<?php
/**
 * System_group Active Record
 * @author  <your-name-here>
 */
class SystemPayments extends TRecord
{
    const TABLENAME = 'system_invoice';
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
        parent::addAttribute('provider');
        parent::addAttribute('amount');
        parent::addAttribute('description');
        parent::addAttribute('date');
    }

    /**
     * Reset aggregates
     */
    public function clearParts()
    {
        // delete the related System_userSystem_user_group objects
        $criteria = new TCriteria;
        $criteria->add(new TFilter('system_user_id', '=', $this->id));

        $repository = new TRepository('SystemUserGroup');
        $repository->delete($criteria);

        $repository = new TRepository('SystemUserProgram');
        $repository->delete($criteria);
    }

    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        // delete the related System_userSystem_user_group objects
        $id = isset($id) ? $id : $this->id;
        $repository = new TRepository('SystemUserGroup');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('system_user_id', '=', $id));
        $repository->delete($criteria);

        // delete the related System_userSystem_user_program objects
        $id = isset($id) ? $id : $this->id;
        $repository = new TRepository('SystemUserProgram');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('system_user_id', '=', $id));
        $repository->delete($criteria);


        // delete the object itself
        parent::delete($id);
    }

}