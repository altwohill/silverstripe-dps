<?php
class EWayPayment extends DataObject {
	public static $db = array(
		'FirstName' => 'Varchar(255)',
		'LastName' => 'Varchar(255)',
		'Address' => 'Varchar(255)',
		'City' => 'Varchar(255)',
		'State' => 'Varchar(50)',
		'PostCode' => 'Varchar(10)',
		'Email' => 'Varchar(255)',
		'Phone' => 'Varchar(20)',
		'InvoiceDescription' => 'Varchar(255)',
		'Reference' => 'Varchar(255)',
		'Amount' => 'Currency',
		'Status' => 'Enum("New, Cancelled, Completed")',
		'AuthCode' => 'Varchar(20)',
		'TansactionNumber' => 'Varchar(20)',
	);
	
	public static $has_one = array(
		'Member' => 'Member'
	);
	
	/**
	 * Gets a read-only fieldset of all the fields
	 */
	public function getViewingFields() {
		$fields = new FieldSet();
		$db = $this->db();
		
		foreach ($db as $fieldName => $fieldType) {
			$fields->push(
				new ReadOnlyField($this->class . $fieldName, $this->FieldLabel($fieldName),
			 	$this->$fieldName)
			);
		}
		
		return $fields;	
	}
}

/**
 * Decorates SiteConfig with eWay customer details. 
 */
class EWay_SiteConfig extends DataObjectDecorator {
	
	public function extraStatics() {
		return array(
			'db' => array(
				'EWay_CustomerID' => 'Varchar(255)',
				'EWay_UserName' => 'Varchar(255)',
				'EWay_CompanyName' => 'Varchar(255)',
				'EWay_PageTitle' => 'Varchar(50)',
				'EWay_PageDescription' => 'Varchar(255)',
				'EWay_PageFooter' => 'Varchar(255)',
				'EWay_Language' => 'Varchar(2)',
				'EWay_Currency' => 'Varchar(5)',
				'EWay_CustomersCanModifyDetails' => 'Boolean',
				'EWay_ServerRequestURL' => 'Varchar(255)',
				'EWay_ServerResultURL' => 'Varchar(255)',
				'EWay_ReturnAction' => 'Varchar(50)',
				'EWay_CancelAction' => 'Varchar(50)',
			),
			'has_one' => array(
				'EWay_CompanyLogo' => 'Image', // Note this will only work if hosted via https
				'EWay_PageBanner' => 'Image',   // Same, and is resized to 960x65
				'EWay_ReturnPage' => 'Page',
				'EWay_CancelPage' => 'Page',
			),
			//@TODO find a way to get these defaults to actually populate
			'defaults' => array(
				'EWay_CustomerID' => '87654321',
				'EWay_UserName' => 'TestAccount',
				'EWay_ServerRequestURL' => 'https://nz.ewaygateway.com/Response/',
				'EWay_ServerResultURL' => 'https://nz.ewaygateway.com/Request/',
				'EWay_Language' => 'EN',
				'EWay_Currency' => 'NZD'
			)
		);
	}
	
	public function updateFieldLabels(array &$labels) {
		$labels = array_merge($labels, array(
			'EWay_CustomerID' => 'Customer ID',
			'EWay_UserName' => 'User Name',
			'EWay_CompanyName' => 'Company Name',
			'EWay_Currency' => 'Currency',
			'EWay_PageTitle' => 'The title text of the payment page',
			'EWay_PageDescription' => 'Used as a greeting message to the customer and is displayed above order details',
			'EWay_PageFooter' => 'Displayed below customer details. Useful for contact details',
			'EWay_CustomersCanModifyDetails' => 'Customers are allowed to use different contact details than what they registered to the site with',
			'EWay_ServerRequestURL' => 'Request URL. NZ Default: https://nz.ewaygateway.com/Response/',
			'EWay_ServerResultURL' => 'Result URL. NZ Default: https://nz.ewaygateway.com/Request/',
			'EWay_ReturnAction' => 'An optional action to run on the return page',
			'EWay_CancelAction' => 'An optional action to run on the cancel page',
		));
	}
	
	public function updateCMSFields(FieldSet &$fields) {
		$statics = $this->extraStatics();
		$payments = $this->owner->scaffoldFormFields(array('restrictFields' => array_keys($statics['db'])));
		
		$payments->removeByName('EWay_Language');
		$payments->insertBefore(
			new DropdownField('EWay_Language', 'Language', array(
				'EN' => 'English',
				'FR' =>'French',
				'DE' => 'German',
				'ES' => 'Spanish',
				'NL' => 'Dutch'
			)),
			'Currency'
		);
		
		$payments->insertBefore(
			new TreeDropdownField("EWay_ReturnPageID", "Choose a page to go to when returning from the payments page", "Page"),
			'EWay_ReturnAction'
		);
		
		$payments->insertBefore(
			new TreeDropdownField("EWay_CancelPageID", "Choose a page to go to when cancelling from the payments page", "Page"),
			'EWay_CancelAction'
		);
		
		
		$payments->push(new ImageField('EWay_CompanyLogo', 'Image to display on secure payment page. WARNING: Only works if site accessible over HTTPS'));
		$payments->push(new ImageField('EWay_PageBanner', 'Banner image to display on secure payment page. WARNING: Only works if site accessible over HTTPS. Will be resized to 960px x 65px'));
			
		$fields->addFieldsToTab('Root.EWayPayments', $payments->dataFields());
	}
}