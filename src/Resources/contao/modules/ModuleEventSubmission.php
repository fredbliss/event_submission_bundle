<?php

namespace IntelligentSpark\EventSubmission\Module;

use Contao\Events as Contao_Events;

/**
 * Class ModuleEventReader
 *
 * Front end module "event reader".
 * @copyright  Leo Feyer 2005-2013
 * @author     Leo Feyer <https://contao.org>
 * @package    Controller
 */

class ModuleEventSubmission extends Contao_Events
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_eventsubmission';


	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### EVENT SUBMISSION ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		return parent::generate();
	}


	/**
	 * Generate the module
	 */
	protected function compile()
	{
        $assetsDir = 'web/bundles/eventsubmission';

        $GLOBALS['TL_JAVASCRIPT'][] = $assetsDir . '/event-submission.min.js|static';
        $GLOBALS['TL_CSS'][] = $assetsDir . '/event-submission.min.css|static';

        $GLOBALS['TL_JQUERY'][] = "<script> jQuery(document).ready(function(){
                    (function($) {

        $('input.time').timepicker({
            'showDuration': true,
            'timeFormat': 'g:i a'
        });

        $('input.date').datepicker({
            'format': 'MM/DD/YYYY',
            'autoclose': true
        });

     })(jQuery);
    });
    </script>
    ";

        $this->tableless = true;

        \System::loadLanguageFile('tl_calendar_events');
        \Controller::loadDataContainer('tl_calendar_events');

        $this->Template->fields = '';
        $this->Template->tableless = $this->tableless;
        $doNotSubmit = false;

        $arrEvent = array();
        $arrFields = array();

        $arrEditable = array(
            'title','location','startDate','endDate','startTime','endTime','details','url','singleSRC','name','email','phone'
        );

        $hasUpload = false;
        $i = 0;

        foreach($arrEditable as $field)
        {
            $arrData = $GLOBALS['TL_DCA']['tl_calendar_events']['fields'][$field];

            if($field=='url') {
                $arrData['label'] = $GLOBALS['TL_LANG']['tl_calendar_events']['event_url'];
                $arrData['eval']['mandatory'] = false;
            }

            $strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

            $strTable = 'tl_calendar_events';

            // Continue if the class is not defined
            if (!class_exists($strClass))
            {
                continue;
            }

            $arrData['eval']['tableless'] = $this->tableless;
            $arrData['eval']['required'] = $arrData['eval']['mandatory'];

            switch($field)
            {
                case 'startDate':
                    $arrData['eval']['class'] = 'date start';
                    break;
                case 'endDate':
                    $arrData['eval']['class'] = 'date end';
                    break;
                case 'startTime':
                    $arrData['eval']['class'] = 'time start';
                    if(\Input::post('FORM_SUBMIT') == 'tl_event_submission')
                        $arrData['eval']['rgxp'] = '';
                    break;
                case 'endTime':
                    $arrData['eval']['class'] = 'time end';
                    if(\Input::post('FORM_SUBMIT') == 'tl_event_submission')
                        $arrData['eval']['rgxp'] = '';
                    break;
            }

            $objWidget = new $strClass($strClass::getAttributesFromDca($arrData, $field, $arrData['default'], $field, $strTable, $this));
            $objWidget->storeValues = true;
            $objWidget->rowClass = 'row_' . $i . (($i == 0) ? ' row_first' : '') . ((($i % 2) == 0) ? ' even' : ' odd');

            // Validate input
            if (\Input::post('FORM_SUBMIT') == 'tl_event_submission')
            {
                $objWidget->validate();
                $varValue = $objWidget->value;

                $rgxp = $arrData['eval']['rgxp'];

                // Convert date formats into timestamps (check the eval setting first -> #3063)
                if (($rgxp == 'date' || $rgxp == 'time' || $rgxp == 'datim') && $varValue != '')
                {
                    try
                    {
                        $objDate = new \Date($varValue);
                        $varValue = $objDate->tstamp;
                    }
                    catch (Exception $e)
                    {
                        $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['invalidDate'], $varValue));
                    }
                }

                // Make sure that unique fields are unique (check the eval setting first -> #3063)
                if ($arrData['eval']['unique'] && $varValue != '')
                {
                    $objUnique = \Database::getInstance()->prepare("SELECT * FROM tl_calendar_events WHERE " . $field . "=?")
                        ->limit(1)
                        ->execute($varValue);

                    if ($objUnique->numRows)
                    {
                        $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], (strlen($arrData['label'][0]) ? $arrData['label'][0] : $field)));
                    }
                }

                if ($objWidget->hasErrors())
                {
                    $doNotSubmit = true;
                }

                // Store current value
                elseif ($objWidget->submitInput())
                {
                    $arrEvent[$field] = $varValue;
                }
            }

            if ($objWidget instanceof uploadable)
            {
                $hasUpload = true;
            }

            $temp = $objWidget->parse();

            $this->Template->fields .= $temp;
            //$arrFields[$arrData['eval']['feGroup']][$field] .= $temp;

            ++$i;
        }

        $this->Template->action = \Environment::get('indexFreeRequest');
        $this->Template->slabel = 'Submit';
        $this->Template->formId = 'tl_event_submission';
        $this->Template->rowLast = 'row_' . ++$i . ((($i % 2) == 0) ? ' even' : ' odd');
        $this->Template->enctype = $hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
        $this->Template->hasError = $doNotSubmit;

        // Create new user if there are no errors
        if (\Input::post('FORM_SUBMIT') == 'tl_event_submission' && !$doNotSubmit)
        {
            $this->createNewEvent($arrEvent);

            $this->jumpToOrReload($this->jumpTo);
        }

	}

    protected function createNewEvent($arrData)
    {
        $arrCal = \StringUtil::deserialize($this->cal_calendar,true);

        $arrData['tstamp'] = time();
        $arrData['pid'] = (integer)current($arrCal);
        $arrData['author'] = 1; //Administrator

        $strContentElement = '<p>'.$arrData['details'].'</p>'."\n\nlink: <a href=\"".$arrData['url']."\" title=\"".$arrData['title']."\" target=\"_blank\">Event Webpage</a>";

        if($arrData['startTime']) {
            $arrData['addTime'] = 1;
            $arrData['startTime'] = strtotime($arrData['startTime']);
            $arrData['endTime'] = strtotime($arrData['endTime']);
        }

        $arrData['alias'] = standardize($this->restoreBasicEntities($arrData['title']));

        // Create Event
        $objNewEvent = \Database::getInstance()->prepare("INSERT INTO tl_calendar_events %s")
            ->set($arrData)
            ->execute();

        $insertId = $objNewEvent->insertId;

        $arrContent['pid'] = $insertId;
        $arrContent['tstamp'] = time();
        $arrContent['sorting'] = 128;
        $arrContent['ptable'] = 'tl_calendar_events';
        $arrContent['type'] = 'text';
        $arrContent['text'] = $strContentElement;
        $arrContent['teaser'] = $strContentElement;

        $objNewContentElement = \Database::getInstance()->prepare("INSERT INTO tl_content %s")
            ->set($arrContent)
            ->execute();

        // Inform admin if no activation link is sent
        $this->sendAdminNotification($insertId, $arrData);
    }

    /**
     * Send an admin notification e-mail
     * @param integer
     * @param array
     */
    protected function sendAdminNotification($intId, $arrData)
    {
        $this->loadLanguageFile('tl_calendar_events');

        $objEmail = new \Email();

        $objEmail->from = $GLOBALS['TL_ADMIN_EMAIL'];
        $objEmail->fromName = $GLOBALS['TL_ADMIN_NAME'];
        $objEmail->subject = sprintf($GLOBALS['TL_LANG']['MSC']['adminSubject'], \Environment::get('host'));

        $strData = "\n\n";

        // Add user details
        foreach ($arrData as $k=>$v)
        {
            if ($k == 'tstamp' || $k == 'author' || $k=='url')
            {
                continue;
            }

            $v = \StringUtil::deserialize($v);

            if ((stripos($k,'date')!==false || stripos($k,'time')!==false) && strlen($v))
            {
                $v = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $v);
            }

            if(array_key_exists($k,$GLOBALS['TL_LANG']['tl_calendar_events']))
                $strData .= $GLOBALS['TL_LANG']['tl_calendar_events'][$k][0] . ': ' . (\is_array($v) ? implode(', ', $v) : $v) . "\n";
        }

        $strDataFinal = sprintf("A new event has been submitted to the website \n event id: %s \n %s", $intId, $strData . "\n") . "\n";

        $objTemplate = new \FrontendTemplate('email_eventsubmission_notify');

        $objEmail->text = $objTemplate->parse();
        $objEmail->text = "\n".$strDataFinal;
        $objEmail->sendBcc('web@brightcloudstudio.com');
		$objEmail->sendCc('info@westfieldbiz.org');
        $objEmail->sendTo($GLOBALS['TL_ADMIN_EMAIL']);//$GLOBALS['TL_ADMIN_EMAIL']);

        $this->log('A new event (ID ' . $intId . ') has been submitted on the website', 'ModuleEventSubmission sendAdminNotification()', TL_ACCESS);

    }
}

?>