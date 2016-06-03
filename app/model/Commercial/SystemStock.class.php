<?php
/**
 * System_group Active Record
 * @author  <your-name-here>
 */
class SystemStock extends TRecord
{
    const TABLENAME = 'system_stock';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}


    private $system_programs = array();

    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('product');
        parent::addAttribute('quantity');
        parent::addAttribute('price');
    }


    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        // delete the related System_userSystem_user_group objects
        $id = isset($id) ? $id : $this->id;

        $repository = new TRepository('SystemStock');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('id', '=', $id));
        $repository->delete($criteria);

        // delete the object itself
        parent::delete($id);
    }

    /*
    * Get de data of an object
    * @param $id object ID
    */
    public function getDataItem($id = NULL)
    {
        $conn = TTransaction::get(); // get PDO connection
        // run query
        $result = $conn->query('SELECT product, quantity, price from system_stock where id = '.$id.' order by id');

        foreach ($result as $key) {
            $this->product = $key['product'];
            $this->quantity = $key['quantity'];
            $this->price = $key['price'];
        }

    }

}
