<?php

namespace Hcode;

class Model
{
    private $values = [];

    //metodo mágico que irá tratar o método
    public function __call($name, $arguments)
    {
        /**variável que irá verificar se o método da classe original é um 
         *  metodo get ou set, pela função substr ficará gravado
         * se é get ou set.
        */
        $method = substr($name, 0, 3);

        /**Vamos ver agora o nome do método que foi 
         * chamado, partindo da posição 3 e indo até
         * o final do nome que foi passado */
        $fieldName = substr($name, 3, strlen($name));

        /**irá tratar o método se for get
         * irá retornar um valor e se for set irá
         * atribuir um valor */
        switch ($method) 
        {
            case 'get':
                return $this->values[$fieldName];
                break;
            case 'set':
                $this->values[$fieldName] = $arguments[0];
                break;
        }
    }

    public function setData($data = array())
    {
        foreach ($data as $key => $value) 
        {
            //será criado os métodos automáticamente
            $this->{"set".$key}($value);
        }
    }

    public function getValues()
    {
        return $this->values;
    }
}

?>