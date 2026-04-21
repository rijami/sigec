<?php

namespace Usuarios\Formularios;

use Zend\Form\Element;
use Zend\Form\Form;

class LoginForm extends Form {

    public function __construct($nonmbre = null) {
        parent::__construct($nonmbre);
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'justify-content-center');
        $this->setAttribute('style', 'padding: 0px 10px');
        $this->add(array(
            'name' => 'login',
            'options' => array(
                'label' => '',
            ),
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control',
                'placeholder' => 'Usuario',
                'autofocus' => 'true',
                'required' => 'true',
                'autocomplete' => 'off',
                'value' => '',
            ),
        ));
        $this->add(array(
            'name' => 'password',
            'options' => array(
                'label' => '',
            ),
            'type' => 'Password',
            'attributes' => array(
                'class' => 'form-control',
                'placeholder' => 'Clave',
                'required' => 'true',
                'autocomplete' => 'off',
                'value' => '',
            ),
        ));
//        $this->add(new Element\Csrf('security'));
        $this->add(array(
            'name' => 'btnIniciarSesion',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Iniciar Sesion',
                'class' => 'btn btn-default'
            ),
        ));
    }
}
