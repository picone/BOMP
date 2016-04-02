<?php
/*
** Zabbix
** Copyright (C) 2001-2015 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


class CFormTable extends CForm {

	private $align;
	private $title;
	private $tableclass = 'formtable';
	protected $top_items = array();
	protected $center_items = array();
	protected $bottom_items = array();

	/**
	 * Help icon.
	 *
	 * @var CHelp
	 */
	protected $help;

	public function __construct($title = null, $action = null, $method = null, $enctype = null, $form_variable = null) {
		$method = is_null($method) ? 'post' : $method;
		parent::__construct($method, $action, $enctype);

		$this->setTitle($title);

		$form_variable = is_null($form_variable) ? 'form' : $form_variable;
		$this->addVar($form_variable, getRequest($form_variable, 1));

        //修改button_itms属性改正其对齐问题
		$this->bottom_items = new CCol(SPACE,SPACE,SPACE,SPACE,SPACE,SPACE,SPACE, 'form_row_last');
		$this->bottom_items->setColSpan(2);
	}

	public function setAction($value) {
		if (is_string($value)) {
			return parent::setAction($value);
		}
		elseif (is_null($value)) {
			return parent::setAction($value);
		}
		else {
			return $this->error('Incorrect value for setAction "'.$value.'".');
		}
	}

	public function setName($value) {
		if (!is_string($value)) {
			return $this->error('Incorrect value for setName "'.$value.'".');
		}
		$this->attr('name', $value);
		$this->attr('id', zbx_formatDomId($value));
		return true;
	}

	public function setAlign($value) {
		if (!is_string($value)) {
			return $this->error('Incorrect value for setAlign "'.$value.'".');
		}
		return $this->align = $value;
	}

	public function setTitle($value = null) {
		$this->title = $value;
	}

	/**
	 * Adds a help icon.
	 */
	public function addHelpIcon() {
		$this->help = new CHelp();
	}

    //修改此方法使其能在一行内容纳更多的item
	public function addRow($item1, $item2 = null, $item3 = null,$item4 = null,$item5 = null,$item6 = null,$item7 = null,$class = null, $id = null) {
		if (is_object($item1) && strtolower(get_class($item1)) === 'crow') {
		}
		elseif (is_object($item1) && strtolower(get_class($item1)) === 'ctable') {
			$td = new CCol($item1, 'form_row_c');
			$td->setColSpan(2);
			$item1 = new CRow($td);
		}
		else {
			if (is_string($item1)) {
				$item1 = nbsp($item1);
			}
			if (empty($item1)) {
				$item1 = SPACE;
			}
            if (is_string($item2)) {
                $item2 = nbsp($item2);
            }
			if (empty($item2)) {
				$item2 = SPACE;
			}
            if (is_string($item3)) {
                $item3 = nbsp($item3);
            }
            if (empty($item3)) {
                $item3 = SPACE;
            }
            if (is_string($item4)) {
                $item4 = nbsp($item4);
            }
            if (empty($item4)) {
                $item4 = SPACE;
            }
            if (is_string($item5)) {
                $item5 = nbsp($item5);
            }
            if (empty($item5)) {
                $item5 = SPACE;
            }
            if (is_string($item6)) {
                $item6 = nbsp($item6);
            }
            if (empty($item6)) {
                $item6 = SPACE;
            }
            if (is_string($item7)) {
                $item7 = nbsp($item7);
            }
            if (empty($item7)) {
                $item7 = SPACE;
            }

			$item1 = new CRow(
				array(
                    SPACE,
					new CCol($item1, 'form_row_1'),
                    SPACE,
					new CCol($item2, 'form_row_2'),
                    SPACE,
                    new CCol($item3, 'form_row_3'),
                    SPACE,
                    new CCol($item4, 'form_row_3'),
                    SPACE,
                    new CCol($item5, 'form_row_3'),
                    SPACE,
                    new CCol($item6, 'form_row_3'),
                    SPACE,
                    new CCol($item7, 'form_row_3'),
                    SPACE
				),
				$class
			);
		}

		if (!is_null($id)) {
			$item1->attr('id', zbx_formatDomId($id));
		}
		array_push($this->center_items, $item1);

		return $item1;
	}

	public function addSpanRow($value, $class = null) {
		if (is_null($value)) {
			$value = SPACE;
		}
		if (is_null($class)) {
			$class = 'form_row_c';
		}
		$col = new CCol($value, $class);
		$col->setColSpan(2);
		array_push($this->center_items, new CRow($col));
	}

	public function addItemToBottomRow($value) {
		$this->bottom_items->addItem($value);
	}

	/**
	 * Sets the class for the table element.
	 *
	 * @param string $class
	 */
	public function setTableClass($class) {
		$this->tableclass = $class;
	}

	public function bodyToString() {
		$res = parent::bodyToString();
		$tbl = new CTable(null, $this->tableclass);
		$tbl->setCellSpacing(0);
		$tbl->setCellPadding(1);
		$tbl->setAlign($this->align);

		// add first row
		if (!is_null($this->title)) {
			$col = new CCol(null, 'form_row_first');
			$col->setColSpan(2);

			if (isset($this->help)) {
				$col->addItem($this->help);
			}
			if (isset($this->title)) {
				$col->addItem($this->title);
			}
			$tbl->setHeader($col);
		}

		// add last row
		$tbl->setFooter($this->bottom_items);

		// add center rows
		foreach ($this->center_items as $item) {
			$tbl->addRow($item);
		}
		return $res.$tbl->toString();
	}
}
