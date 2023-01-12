<?php

/**
 * noxHtmlForm
 *
 * Класс для создания HTML форм из баз данных
 *
 * @author      <pa-nic@yandex.ru>
 * @version    1.0
 * @package    nox-system
 * @subpackage output
 * TODO: унифицированный массив данных
 */

class noxModelForm {

    public $onlyFields;
    public $fieldsParams;
    public $model;

    protected $formNamesPrefix;
    protected $fields;
    protected $formParams;
    protected $formValues;
    protected $defaultValues;
    protected $view = '';
    protected $htmlDecoration = TRUE;

    private $appendForms = array();
    private $saveForm = false;

    public function getFormInputName() {
        return $this->formNamesPrefix;
    }

    public function getSaveMode() {
        return $this->saveForm;
    }

    public function setSaveMode($mode) {
        $this->saveForm = (bool)$mode;
    }

    /**
     * @param mixed $source noxModel|string #Источник данных
     * @param mixed $formParams             #Параметры формы
     * @param mixed $defaultValues          #Значения для полей ввода по умолчанию
     * @param bool $onlyFields              #Вернуть только поля ввода или конечную форму
     * @throws noxException
     */
    public function __construct($source, $formParams = false, $defaultValues = false, $onlyFields = false) {
        $this->onlyFields = (bool)$onlyFields;
        if($source instanceof noxModel) {
            $this->model = $source;
            $this->fields = $this->model->db->scheme($this->model->table);
        }
        elseif(is_string($source)) {
            try {
                $this->model = new noxModel(false, $source);
                $this->fields = $this->model->db->scheme($this->model->table);
            } catch(noxException $e) {
                throw new noxException('Невозможно представить ' . $source . ' в качестве объекта данных noxDbForm!');
            }
        } else {
            throw new noxException('Невозможно представить ' . @$source . ' в качестве объекта данных noxDbForm!');
        }

        $this->formNamesPrefix = $this->_hash($this->model->table);

        if($defaultValues) {
            $this->setValues($defaultValues);
        }
        else {
            $this->formValues = $this->_defaultValues();
        }

        if(!$onlyFields) {
            $defaultFormParams = [
                'url'       => noxSystem::$fullUrl,
                'method'    => 'post',
                'enctype'   => ''
            ];

            if(is_array($formParams)) {
                foreach($defaultFormParams as $key=>$val) {
                    if(!isset($formParams[$key])) {
                        $formParams[$key] = $val;
                    }
                }
                $this->formParams = $formParams;
            }
            else {
                $this->formParams = $defaultFormParams;
            }
        }

        if($this->saveForm) $this->_saveForm();
    }

    public function addFieldsParams($params) {
        if(!is_array($params) || empty($params)) return;
        foreach($params as $name => $param) {
            if(isset($this->fields[$name])) {
                if(isset($this->fields[$name]['params'])) {
                    $this->fields[$name]['params'] += $param;
                }
                    $this->fields[$name]['params'] = $param;
            }
        }
    }

    /**
     * @param mixed $fields
     * @return noxModelForm
     */
    public function acceptedFields($fields) {
        if(is_string($fields)) {
            $fields = explode(',', $fields);
            foreach($fields as &$f) {
                $f = ltrim($f);
            }
        }

        foreach($this->fields as $field=>$_) {
            if(!in_array($field, $fields))
                unset($this->fields[$field]);
        }

        return $this;
    }

    /**
     * @param noxModelForm $form
     * @return noxModelForm
     * @description Позволяет присоединить дополнительно поля ввода другой формы
     */
    public function attachForm($form) {
        if($form instanceof noxModelForm) {
            $form->onlyFields = true;
            $this->appendForms[$form->getFormInputName()] = $form;
        }

        return $this;
    }

    public function setValues($values) {
        if(is_array($values)) {
            foreach($values as $k=>$v) {
                $this->formValues[$k] = $v;
            }
        }
        else {
            $this->formValues = $this->_defaultValues();
        }
        return $this;
    }

    /**
     * @return array
     */
    private function _defaultValues() {
        $ar = [];
        foreach($this->fields as $name=>$field) {
            $ar[$name] = $this->formValues[$name];
        }
        return $ar;
    }

    /**
     * @param string $value
     * @return string
     */
    private function _hash($value) {
        return md5($value);
    }

    private function _formParamsToString() {
        $f = '';
        foreach($this->formParams as $key=>$val) {
            $f .= ' ' . $key . '="' . urlencode($val) . '"';
        }
        return $f;
    }

    public function __toString() {
        if(!$this->onlyFields)
            $this->view .= '<form' . $this->_formParamsToString() . '>';
        if($this->htmlDecoration)
            $this->view .= '<div id="form_' . $this->formNamesPrefix . '">';

        foreach($this->fields as $name=>$field) {
            $input = '';
            $type = $field['type'];

            if($field['key'] === 'pri') {
                $this->view .= '<input name="' . $this->formNamesPrefix . '[' . $name . ']" type="hidden" value="' . htmlspecialchars($this->formValues[$name]) . '" />';
                continue;
            }

            if(isset($field['ref'])) {
                $type = '_foreign';
            }

            if($field['key'] !== 'pri') {
                switch($type) {
                    case 'char':
                    case 'varchar':
                    case 'year':
                        $input = '<input name="' . $this->formNamesPrefix . '[' . $name . ']" type="text" maxlength="' . $field['length'] . '" value="' . htmlspecialchars($this->formValues[$name]) . '"';
                        if(isset($field['params'])) {
                            foreach($field['params'] as $k=>$v) {
                                $input .= ' ' . htmlspecialchars($k) . '="' . htmlspecialchars($v) . '"';
                            }
                        }
                        $input .= ' />';

                        break;

                    case 'tinyint':
                        if($field['length'] == 1) {
                            $input = '<input name="' . $this->formNamesPrefix . '[' . $name . ']" type="checkbox"';
                            if($this->formValues[$name])
                                $input .= ' checked="checked"';
                            if(isset($field['params'])) {
                                foreach($field['params'] as $k=>$v) {
                                    $input .= ' ' . htmlspecialchars($k) . '="' . htmlspecialchars($v) . '"';
                                }
                            }
                            $input .= ' />';
                        }

                        break;

                    case 'enum':
                    case 'set':
                        $options = explode(',', $field['length']);
                        $input = '<select name="' . $this->formNamesPrefix . '[' . $name . ']"';
                        if($field['type'] === 'set')
                            $input .= ' multiple';
                        if(isset($field['params'])) {
                            foreach($field['params'] as $k=>$v) {
                                $input .= ' ' . htmlspecialchars($k) . '="' . htmlspecialchars($v) . '"';
                            }
                        }
                        $input .= '>';

                        foreach($options as $opt) {
                            $opt = htmlspecialchars(str_replace("'", '', $opt));
                            $input .= '<option value="' . $opt . '"';
                            if($this->formValues[$name] === $opt)
                                $input .= ' selected="selected"';
                            $input .= '>' . $opt . '</option>';
                        }

                        $input .= '</select>';

                        break;

                    case '_foreign':
                        $input = '<select name="' . $this->formNamesPrefix . '[' . $name . ']"';
                        if(isset($field['params'])) {
                            foreach($field['params'] as $k=>$v) {
                                $input .= ' ' . htmlspecialchars($k) . '="' . htmlspecialchars($v) . '"';
                            }
                        }
                        $input .= '>';

                        $ref = $field['ref'];

                        $refScheme = $this->model->db->scheme($ref['db'] . '.' . $ref['table']);

                        if(isset($refScheme['name']))
                            $refSqlSelect = 'name';
                        elseif(isset($refScheme['title']))
                            $refSqlSelect = 'title as name';
                        else
                            $refSqlSelect = $ref['column'] . ' as name';

                        $sql = 'select ' . $refSqlSelect . ',' . $ref['column'] . ' FROM ' . $ref['db'] . '.' . $ref['table'];
                        if(isset($this->model->fields[$name]['sql_where'])) {
                            $sql .= ' WHERE ' . $this->model->fields[$name]['sql_where'];
                        }
                        $sql .= ' ORDER by name';
                        $this->model->reset()->exec($sql);
                        $options = $this->model->fetchAll('id', 'name');

                        if($field['null'])
                            $input .= '<option value="">-----</option>';

                        foreach($options as $key=>$val) {
                            $input .= '<option value="' . $key . '"' . ((@$this->formValues[$name] == $key) ? ' selected' : '') . '>' . htmlspecialchars($val) . '</option>';
                        }

                        $input .= '</select>';
                        break;

                    default:
                        $input = '<input name="' . $this->formNamesPrefix . '[' . $name . ']" type="text" value="' . htmlspecialchars($this->formValues[$name]);
                        if(isset($field['params'])) {
                            foreach($field['params'] as $k=>$v) {
                                $input .= ' ' . htmlspecialchars($k) . '="' . htmlspecialchars($v) . '"';
                            }
                        }
                        $input .= '" />';

                }
            }

            $this->view .= sprintf('<p><label>%s</label><span>%s</span></p>', _t($this->model->table . '.' . $name), $input);

        }

        if($this->appendForms) {
            foreach($this->appendForms as $form) {
                /** @var noxModelForm $form*/
                $this->view .= $form->__toString();
            }
        }

        if(!$this->onlyFields)
            $this->view .= '<p><input type="submit" value="Сохранить" /></p>';

        if($this->htmlDecoration)
            $this->view .= '</div>';

        if(!$this->onlyFields)
            $this->view .= '</form>';
        return $this->view;
    }

    public function _saveForm() {
        $ar = ($this->formParams['method'] === 'GET') ? $_GET : $_POST;
        if(isset($ar[$this->formNamesPrefix])) {
            $data = $ar[$this->formNamesPrefix];

            foreach($this->fields as $name => $field) {
                if(!isset($data[$name])) {
                    $data[$name] = $this->formValues[$name];
                }
            }
            _d($data);
            if(isset($data[$this->model->id_field])) {
                _d('REPLACE ');
                //$this->model->replace($data);
            }
            else {
                _d('INSERT ');
                //$this->model->insert($data);
            }
        }
        if(!empty($this->appendForms)) {
            foreach($this->appendForms as $form) {
                /** @var noxModelForm $form*/
                $form->_saveForm();
            }
        }
    }
}
