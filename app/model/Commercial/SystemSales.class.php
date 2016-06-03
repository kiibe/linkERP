<?php
/**
 * System_group Active Record
 * @author  <your-name-here>
 */
class SystemSales extends TRecord
{
    const TABLENAME = 'system_sales';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}

    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('date');
        parent::addAttribute('client');
        parent::addAttribute('amount');
    }

    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        // delete the related System_userSystem_user_group objects
        $id = isset($id) ? $id : $this->id;
        $repository = new TRepository('SystemSales');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('system_user_id', '=', $id));
        $repository->delete($criteria);

        // delete the related System_userSystem_user_program objects
        /*$id = isset($id) ? $id : $this->id;
        $repository = new TRepository('SystemSales');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('system_user_id', '=', $id));
        $repository->delete($criteria);*/


        // delete the object itself
        parent::delete($id);
    }

    public function saveSale($client_insert, $amount_insert)
    {
        try
        {
        $conn = TTransaction::get(); // get PDO connection
        // run query
        $date = date('Y-m-d');

        $result = $conn->query('INSERT INTO system_sales (date, client, amount) VALUES ('.$date.', '.$client_insert.', '.$amount_insert.')');

        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }

    public function saveLines($id, $products)
    {

    }

}
