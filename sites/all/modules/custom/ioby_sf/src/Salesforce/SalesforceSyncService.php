<?php

namespace Drupal\ioby_sf\Salesforce;

use Drupal\ioby_sf\Salesforce\Models\Account;
use Drupal\ioby_sf\Salesforce\Models\Affiliation;
use Drupal\ioby_sf\Salesforce\Models\CampaignMember;
use Drupal\ioby_sf\Salesforce\Models\Contact;
use Drupal\ioby_sf\Salesforce\Models\Campaign;
use Drupal\ioby_sf\Salesforce\Models\Fund;
use Drupal\ioby_sf\Salesforce\Models\FundCampaignGrouping;
use Drupal\ioby_sf\Salesforce\Models\Opportunity;
use Drupal\ioby_sf\Salesforce\Models\ProjectParticipation;
use Drupal\ioby_sf\Salesforce\Repository\SalesforceRepository;

class SalesforceSyncService
{
  // Constants for Salesforce IDs to specific record types.
  const RECORD_TYPE_ACCOUNT = '012F0000001ElJH';
  const RECORD_TYPE_CAMPAIGN = '012F0000000j8YG';
  const RECORD_TYPE_CAMPAIGN_MEMBER = '012F0000000jKUT';
  const RECORD_TYPE_CONTACT = '012F0000000j8YB';
  const RECORD_TYPE_OPPORTUNITY = '012A0000000JhLP';
  const RECORD_TYPE_POTENTIAL_PROJECT = '012F0000001EwRY';
  const RECORD_TYPE_PROJECT = '012F0000000j8YGIAY';
  const RECORD_TYPE_ORDER = '01I1b0000004O6U';

  // Constants for the names of objects in Salesforce.
  const OBJECT_TYPE_ACCOUNT = 'Account';
  const OBJECT_TYPE_AFFILIATION = 'npe5__Affiliation__c';
  const OBJECT_TYPE_CAMPAIGN = 'Campaign';
  const OBJECT_TYPE_CAMPAIGN_MEMBER = 'CampaignMember';
  const OBJECT_TYPE_CONTACT = 'Contact';
  const OBJECT_TYPE_FUND = 'Fund__c';
  const OBJECT_TYPE_FUND_CAMPAIGN_GROUPING = 'Grouping__c';
  const OBJECT_TYPE_OPPORTUNITY = 'Opportunity';
  const OBJECT_TYPE_PROJECT_PARTICIPATION = 'Project_Participation__c';
  const OBJECT_TYPE_ORDER = 'Order__c';
  const OBJECT_TYPE_ORDER_LINE_ITEM = 'Order_Line_Item__c';

  // The maximum number of records that you can bulk insert/update in a single API call
  const SALESFORCE_API_LIMIT = 200;

  /**
   * @var \SforceEnterpriseClient
   */
  private $client;

  /**
   * @var string
   */
  private $wsdl;

  /**
   * @var string
   */
  private $username;

  /**
   * @var string
   */
  private $password;

  /**
   * @var bool
   */
  private $isConnected = FALSE;

  public function __construct($wsdl, $username, $password) {
    $this->wsdl = $wsdl;
    $this->username = $username;
    $this->password = $password;
  }

  /**
   * @param Account[] $accounts
   *
   * @throws \Exception
   */
  public function pushAccountObjects(array $accounts) {
    if (!$this->isConnected) {
      $this->connect();
    }

    $sf_objects = array();

    if (count($accounts) > self::SALESFORCE_API_LIMIT) {
      throw new \InvalidArgumentException("Error pushing account objects: Exceeds the maximum number of objects that can be passed to Salesforce at one time.");
    }

    foreach ($accounts as $account) {
      $sf_object = new \stdClass();

      $sf_object->Drupal_ID__c = $account->getAccountId();


      if ($account->getSalesforceRecordId() == NULL) {
        $sf_object->Name = $account->getAccountName();
        $sf_object->RecordTypeId = self::RECORD_TYPE_ACCOUNT;
      }

      if ($account->getSalesforceRecordId() == NULL || $account->getNeedsUpdate()) {
        $sf_object->BillingStreet = $account->getBillingStreet();
        $sf_object->BillingCity = $account->getBillingCity();
        $sf_object->BillingState = $account->getBillingState();
        $sf_object->BillingPostalCode = $account->getBillingZip(10);
        $sf_object->BillingCountry = $account->getBillingCountry();
      }

      $sf_objects[] = $sf_object;
    }

    if (!empty($sf_objects)) {
      try {
        $results = $this->client->upsert('Drupal_ID__c', $sf_objects, self::OBJECT_TYPE_ACCOUNT);
        foreach ($results as $index => $result) {
          if (!empty($result->success)) {
            db_update('ioby_sf_accounts')
              ->fields(
                array(
                  'salesforce_record_id' => $result->id,
                  'needs_update' => intval(FALSE),
                )
              )
              ->condition('account_id', $accounts[$index]->getAccountId())
              ->execute();
          }
          else {
            watchdog('ioby_sf', 'Received an error from Salesforce when trying to upsert account with id: @account_id. Errors: @errors',
              array('@account_id' => $accounts[$index]->getAccountId(), '@errors' => print_r($result->errors, TRUE)), WATCHDOG_ERROR);
          }
        }
      }
      catch (\Exception $e) {
        throw new \Exception("There was a problem pushing account objects to Salesforce.", 0, $e);
      }
    }
  }

  /**
   * @param Campaign[] $campaigns
   *
   * @throws \InvalidArgumentException
   */
  public function pushCampaignObjects(array $campaigns, $newCampaigns = TRUE) {
    if (!$this->isConnected) {
      $this->connect();
    }

    $operation = $newCampaigns ? 'create' : 'update';

    $sf_objects = array();

    if (count($campaigns) > self::SALESFORCE_API_LIMIT) {
      throw new \InvalidArgumentException("Error pushing campaign objects: Exceeds the maximum number of objects that can be passed to Salesforce at one time.");
    }

    foreach ($campaigns as $campaign) {
      $sf_object = new \stdClass();
      $sf_object->Status = $campaign->getStatus();
      $sf_object->Node_ID__c = $campaign->getProjectNid();
      $sf_object->ExpectedRevenue = $campaign->getExpectedRevenue();
      $sf_object->StartDate = $campaign->getStartDate();
      $sf_object->Name = $campaign->getName(80);
      $sf_object->Project_Deadline__c = $campaign->getDeadlineDate();

      if (empty($sf_object->Project_Deadline__c)) {
        unset($sf_object->Project_Deadline__c);
        $sf_object->fieldsToNull[] = 'Project_Deadline__c';
      }

      if ($newCampaigns) {
        $sf_object->Full_Project_Name__c = $campaign->getName();
        $sf_object->RecordTypeId = self::RECORD_TYPE_PROJECT;
        $sf_object->Volunteers_Accepted__c = $campaign->getVolunteersAccepted();
        $sf_object->Volunteer_Needs__c = $campaign->getVolunteersDescription(TRUE);
        $sf_object->CampaignMemberRecordTypeId = self::RECORD_TYPE_CAMPAIGN_MEMBER;
        $sf_object->Project_Street_1__c = $campaign->getProjectStreet();
        $sf_object->Project_Street_2__c = $campaign->getProjectStreet2();
        $sf_object->Project_City__c = $campaign->getProjectCity();
        $sf_object->Project_State__c = $campaign->getProjectState();
        $sf_object->Project_Zip__c = $campaign->getProjectZip();
        $sf_object->Project_Borough__c = $campaign->getProjectBorough();
        $sf_object->id = $campaign->getSalesforceRecordId();
      }
      else {
        $sf_object->id = $campaign->getSalesforceRecordId();
      }

      $sf_objects[] = $sf_object;
    }

    if (!empty($sf_objects)) {
      try {
        // Whether this project is new or being updated, perform an upsert.
        $results = $this->client->upsert('Node_ID__c', $sf_objects, self::OBJECT_TYPE_CAMPAIGN);

        foreach ($results as $index => $result) {
          if (!empty($result->success)) {
            // Update the campaigns table to indicate that we've synced this row.
            db_update('ioby_sf_campaigns')
              ->fields(
                array(
                  'salesforce_record_id' => $result->id,
                  'needs_update' => intval(FALSE),
                  'is_new' => intval(FALSE),
                )
              )
              ->condition('project_nid', $sf_objects[$index]->Node_ID__c)
              ->execute();

            // Update the potential_projects table to indicate that we've synced
            // this project.
            db_update('ioby_sf_potential_projects')
              ->fields(
                array(
                  'salesforce_record_id' => $result->id,
                  'connected_to_sf' => intval(TRUE),
                  'create_new_sf_object' => intval(FALSE),
                )
              )
              ->condition('nid', $sf_objects[$index]->Node_ID__c)
              ->execute();
          }
          else {
            watchdog('ioby_sf', 'Received an error from Salesforce when trying to @operation campaign with project_nid: @project_nid. Errors: @errors',
              array(
                '@operation' => $operation,
                '@project_nid' => $sf_objects[$index]->Node_ID__c,
                '@errors' => print_r($result->errors, TRUE)
              ), WATCHDOG_ERROR);
          }
        }
      }
      catch (\Exception $e) {
        if ($newCampaigns) {
          throw new \Exception("There was a problem pushing new campaign objects to Salesforce.", 0, $e);
        }
        else {
          throw new \Exception("There was a problem pushing updated campaign objects to Salesforce.", 0, $e);
        }
      }
    }
  }

  /**
   * @param Fund[] $funds
   *
   * @throws \InvalidArgumentException
   * @throws \Exception
   */
  public function pushFundObjects(array $funds) {
    if (!$this->isConnected) {
      $this->connect();
    }

    $sf_objects = array();

    if (count($funds) > self::SALESFORCE_API_LIMIT) {
      throw new \InvalidArgumentException("Error pushing fund objects: Exceeds the maximum number of objects that can be passed to Salesforce at one time.");
    }

    foreach ($funds as $fund) {
      $sf_object = new \stdClass();

      $sf_object->Drupal_ID__c = $fund->getCampaignNid();
      $sf_object->Total_Fund_Value__c = $fund->getTotalValue();
      $sf_object->Type__c = $fund->getFundType();
      $sf_object->Account__c = $fund->getAccountSfId();

      if ($fund->getSalesforceRecordId() == NULL) {
        $sf_object->Name = $fund->getName(80);
        $sf_object->Description__c = $fund->getDescription();
      }

      $sf_objects[] = $sf_object;
    }

    if (!empty($sf_objects)) {
      try {
        $results = $this->client->upsert('Drupal_ID__c', $sf_objects, self::OBJECT_TYPE_FUND);
        foreach ($results as $index => $result) {
          if (!empty($result->success)) {
            db_update('ioby_sf_funds')
              ->fields(
                array(
                  'salesforce_record_id' => $result->id,
                  'needs_update' => intval(FALSE),
                )
              )
              ->condition('campaign_nid', $funds[$index]->getCampaignNid())
              ->execute();
          }
          else {
            watchdog('ioby_sf', 'Received an error from Salesforce when trying to upsert fund with campaign_nid: @campaign_nid. Errors: @errors',
              array('@campaign_nid' => $funds[$index]->getCampaignNid(), '@errors' => print_r($result->errors, TRUE)), WATCHDOG_ERROR);
          }
        }
      }
      catch (\Exception $e) {
        throw new \Exception("There was a problem pushing fund objects to Salesforce.", 0, $e);
      }
    }
  }

  /**
   * @param Contact[] $contacts
   *
   * @throws \InvalidArgumentException
   * @throws \Exception
   */
  public function pushContactObjects(array $contacts) {
    if (!$this->isConnected) {
      $this->connect();
    }

    $sf_objects = array();

    if (count($contacts) > self::SALESFORCE_API_LIMIT) {
      throw new \InvalidArgumentException("Error pushing contact objects: Exceeds the maximum number of objects that can be passed to Salesforce at one time.");
    }

    foreach ($contacts as $contact) {
      $sf_object = new \stdClass();
      $sf_object->FirstName = $contact->getFirstName();
      $sf_object->LastName = $contact->getLastName();

      $birth_date = $contact->getBirthDate();

      $sf_object->Email = $contact->getEmail();
      if (!empty($birth_date)) {
        $sf_object->Birthdate = $birth_date;
      }

      if ($contact->getUpdateMailingAddress()) {
        $sf_object->MailingStreet = $contact->getMailingStreet();
        $sf_object->MailingCity = $contact->getMailingCity();
        $sf_object->MailingState = $contact->getMailingState();
        $sf_object->MailingPostalCode = $contact->getMailingZip(10);
        $sf_object->MailingCountry = $contact->getMailingCountry();
        $sf_object->Phone = $contact->getPhone(40);
      }
      if ($contact->getUpdateOtherAddress()) {
        $sf_object->npe01__AlternateEmail__c = $contact->getAlternateEmail();
        $sf_object->OtherStreet = $contact->getOtherStreet();
        $sf_object->OtherCity = $contact->getOtherCity();
        $sf_object->OtherState = $contact->getOtherState();
        $sf_object->OtherPostalCode = $contact->getOtherZip(10);
        $sf_object->OtherCountry = $contact->getOtherCountry();
        $sf_object->Phone = $contact->getPhone(40);
        $sf_object->OtherPhone = $contact->getOtherPhone(40);
      }

      if ($contact->getSalesforceRecordId() == NULL) {
        $sf_object->RecordTypeId = self::RECORD_TYPE_CONTACT;
      }

      $sf_objects[] = $sf_object;
    }

    try {
      $results = $this->client->upsert('Email', $sf_objects, self::OBJECT_TYPE_CONTACT);
      foreach ($results as $index => $result) {
        if (!empty($result->success)) {
          // We might have a duplicate record ID.
          if ($this->contactExists($result)) {
            // Duplicate ID. We need to remove the ID-less record in the sync
            // table with this email, then merge this information with the entry
            // already attached to the ID. See IOBY-92 for details.
            watchdog('ioby_sf', 'Contact with email address @email and SalesForce Record ID @id already exists.', array(
              '@email' => $contacts[$index]->getEmail(),
              '@id' => $result->id,
            ), WATCHDOG_WARNING);

            // Remove any entries with this same email address but without an
            // ID, and then update the existing contact.
            $this->removeContact($contacts[$index]);
            $this->updateContact($contacts[$index], $result);
          }
          else {
            db_update('ioby_sf_contacts')
              ->fields(
                array(
                  'salesforce_record_id' => $result->id,
                  'needs_update' => intval(FALSE),
                  'update_mailing_address' => intval(FALSE),
                  'update_other_address' => intval(FALSE),
                )
              )
              ->condition('email', $contacts[$index]->getEmail())
              ->execute();
          }
        }
        else {
          if (!empty($result->errors[0]->statusCode) && $result->errors[0]->statusCode == 'DUPLICATE_EXTERNAL_ID') {
            // TODO: This is a temporary fix until contact merging functionality has been added
            $contact = $contacts[$index];
            $sf_object = $sf_objects[$index];

            if ($contact->getSalesforceRecordId() != NULL) {
              $sf_object->Id = $contact->getSalesforceRecordId();
              unset($sf_object->Email, $sf_object->RecordTypeId);

              $update_result = $this->client->update(array($sf_object), self::OBJECT_TYPE_CONTACT);
              if (!empty($update_result[0]->success)) {
                db_update('ioby_sf_contacts')
                  ->fields(
                    array(
                      'needs_update' => intval(FALSE),
                      'update_mailing_address' => intval(FALSE),
                      'update_other_address' => intval(FALSE),
                    )
                  )
                  ->condition('salesforce_record_id', $contact->getSalesforceRecordId())
                  ->execute();
              }
              else {
                watchdog('ioby_sf', 'Received an error from Salesforce when trying to update contact with Salesforce ID: @Id. Errors: @errors',
                  array(
                    '@Id' => $sf_object->Id,
                    '@errors' => print_r($update_result[0]->errors, TRUE)
                  ), WATCHDOG_ERROR);
              }
            }
            else {
              // There are multiple contact records in Salesforce with the same
              // email address but none of them came from Drupal so just create
              // a new record in SF for now
              $create_result = $this->client->create(array($sf_object), self::OBJECT_TYPE_CONTACT);

              if (!empty($create_result[0]->success)) {
                db_update('ioby_sf_contacts')
                  ->fields(
                    array(
                      'salesforce_record_id' => $create_result[0]->id,
                      'needs_update' => intval(FALSE),
                      'update_mailing_address' => intval(FALSE),
                      'update_other_address' => intval(FALSE),
                    )
                  )
                  ->condition('email', $contacts[$index]->getEmail())
                  ->execute();
              }
              else {
                watchdog('ioby_sf', 'Received an error from Salesforce when trying to create contact with email: @email. Errors: @errors',
                  array(
                    '@email' => $contact->getEmail(),
                    '@errors' => print_r($create_result[0]->errors, TRUE)
                  ), WATCHDOG_ERROR);
              }
            }
          }
          else {
            watchdog('ioby_sf', 'Received an error from Salesforce when trying to upsert contact with email: @email. Errors: @errors',
              array(
                '@email' => $contacts[$index]->getEmail(),
                '@errors' => print_r($result->errors, TRUE)
              ), WATCHDOG_ERROR);
          }
        }
      }
    }
    catch (\Exception $e) {
      throw new \Exception("There was a problem pushing contact objects to Salesforce.", 0, $e);
    }
  }

  /**
   * Tells you if a contact with this same SalesForce Record ID already exists
   * in the "transitional" sync table.
   *
   * @param $result
   *    The result object from SalesForce.
   * @return bool
   *
   * @author Paul Venuti
   */
  protected function contactExists($result) {
    return db_select('ioby_sf_contacts', 'c')
      ->fields('c', array('salesforce_record_id'))
      ->condition('c.salesforce_record_id', $result->id)
      ->execute()
      ->fetchField();
  }

  /**
   * Removes a contact from the "transitional" sync table.
   *
   * @param \Drupal\ioby_sf\Salesforce\Models\Contact $contact
   *    The contact to remove.
   *
   * @author Paul Venuti
   */
  protected function removeContact(Contact $contact) {
    $email = $contact->getEmail();
    db_delete('ioby_sf_contacts')
      ->condition('email', $email)
      ->isNull('salesforce_record_id')
      ->execute();

    watchdog('ioby_sf', 'Removed contact with email @email', array('@email' => $email), WATCHDOG_INFO);
  }

  /**
   * Merges a contact in the "transitional" users table using data from the most
   * recent SalesForce sync.
   *
   * @param \Drupal\ioby_sf\Salesforce\Models\Contact $contact
   *    The contact to update.
   * @param $result
   *    The result object from SalesForce for this contact.
   *
   * @author Paul Venuti
   */
  protected function updateContact(Contact $contact, $result) {
    try {
      db_merge('ioby_sf_contacts')
        ->key(array('salesforce_record_id' => $result->id))
        ->fields(array(
          'email' => $contact->getEmail(),
          'uid' => $contact->getUid(), // Not sure if we need this or not
          'first_name' => $contact->getFirstName(),
          'last_name' => $contact->getLastName(),
          'birth_date' => $contact->getBirthDate(),
          'mailing_street' => $contact->getMailingStreet(),
          'mailing_city' => $contact->getMailingCity(),
          'mailing_state' => $contact->getMailingState(),
          'mailing_zip' => $contact->getMailingZip(),
          'mailing_country' => $contact->getMailingCountry(),
          'other_street' => $contact->getOtherStreet(),
          'other_city' => $contact->getOtherCity(),
          'other_state' => $contact->getOtherState(),
          'other_zip' => $contact->getOtherZip(10),
          'other_country' => $contact->getOtherCountry(),
          'alternate_email' => $contact->getAlternateEmail(),
          'phone' => $contact->getPhone(40),
          'other_phone' => $contact->getOtherPhone(40),
          'needs_update' => intval(FALSE),
          'update_mailing_address' => intval(FALSE),
          'changed' => REQUEST_TIME,
        ))
        ->execute();
    }
    catch (\Exception $e) {
      ioby_sf_handle_exceptions($e);
    }
  }

  /**
   * @param FundCampaignGrouping[] $groupings
   *
   * @throws \InvalidArgumentException
   * @throws \Exception
   */
  public function pushFundGroupingObjects(array $groupings) {
    if (!$this->isConnected) {
      $this->connect();
    }

    $sf_objects = array();

    if (count($groupings) > self::SALESFORCE_API_LIMIT) {
      throw new \InvalidArgumentException("Error pushing campaign objects: Exceeds the maximum number of objects that can be passed to Salesforce at one time.");
    }

    foreach ($groupings as $grouping) {
      if ($grouping->getSalesforceRecordId() != NULL) {
        throw new \Exception("Fund Grouping with fund_grouping_id: {$grouping->getFundGroupingId()} already has a Salesforce record Id.");
      }

      $sf_object = new \stdClass();

      $sf_object->Drupal_ID__c = $grouping->getFundGroupingId();
      $sf_object->Campaign__c = $grouping->getCampaignSfId();
      $sf_object->Fund__c = $grouping->getFundSfId();

      $sf_objects[] = $sf_object;
    }

    if (!empty($sf_objects)) {
      try {
        $results = $this->client->upsert('Drupal_ID__c', $sf_objects, self::OBJECT_TYPE_FUND_CAMPAIGN_GROUPING);
        foreach ($results as $index => $result) {
          if (!empty($result->success)) {
            db_update('ioby_sf_fund_groupings')
              ->fields(
                array(
                  'salesforce_record_id' => $result->id,
                )
              )
              ->condition('fund_grouping_id', $groupings[$index]->getFundGroupingId())
              ->execute();
          }
          else {
            watchdog('ioby_sf', 'Received an error from Salesforce when trying to upsert fund campaign grouping with fund_grouping_id: @fund_grouping_id. Errors: @errors',
              array('@fund_grouping_id' => $groupings[$index]->getFundGroupingId(), '@errors' => print_r($result->errors, TRUE)), WATCHDOG_ERROR);
          }
        }
      }
      catch (\Exception $e) {
        throw new \Exception("There was a problem pushing fund grouping objects to Salesforce.", 0, $e);
      }
    }
  }

  /**
   * @param Affiliation[] $affiliations
   *
   * @throws \InvalidArgumentException
   * @throws \Exception
   */
  public function pushAffiliationObjects(array $affiliations) {
    if (!$this->isConnected) {
      $this->connect();
    }

    $sf_objects = array();

    if (count($affiliations) > self::SALESFORCE_API_LIMIT) {
      throw new \InvalidArgumentException("Error pushing affiliation objects: Exceeds the maximum number of objects that can be passed to Salesforce at one time.");
    }

    foreach ($affiliations as $affiliation) {
      if ($affiliation->getSalesforceRecordId() != NULL) {
        throw new \Exception("Affiliation with affiliation_id: {$affiliation->getAffiliationId()} already has a Salesforce record Id.");
      }

      $sf_object = new \stdClass();
      $sf_object->Drupal_ID__c = $affiliation->getAffiliationId();
      $sf_object->npe5__Contact__c = $affiliation->getContactSfId();
      $sf_object->npe5__Organization__c = $affiliation->getAccountSfId();
      $sf_object->npe5__Status__c = "Current";

      $sf_objects[] = $sf_object;
    }

    if (!empty($sf_objects)) {
      try {
        $results = $this->client->upsert('Drupal_ID__c', $sf_objects, self::OBJECT_TYPE_AFFILIATION);
        foreach ($results as $index => $result) {
          if (!empty($result->success)) {
            db_update('ioby_sf_affiliations')
              ->fields(
                array(
                  'salesforce_record_id' => $result->id,
                )
              )
              ->condition('affiliation_id', $affiliations[$index]->getAffiliationId())
              ->execute();
          }
          else {
            watchdog('ioby_sf', 'Received an error from Salesforce when trying to upsert affiliation with affiliation_id: @affiliation_id. Errors: @errors',
              array('@affiliation_id' => $affiliations[$index]->getAffiliationId(), '@errors' => print_r($result->errors, TRUE)), WATCHDOG_ERROR);
          }
        }
      }
      catch (\Exception $e) {
        throw new \Exception("There was a problem pushing affiliation objects to Salesforce.", 0, $e);
      }
    }
  }

  /**
   * @param ProjectParticipation[] $project_participations
   *
   * @throws \InvalidArgumentException
   * @throws \Exception
   */
  function pushProjectParticipationObjects(array $project_participations) {
    if (!$this->isConnected) {
      $this->connect();
    }

    $sf_objects = array();

    if (count($project_participations) > self::SALESFORCE_API_LIMIT) {
      throw new \InvalidArgumentException("Error pushing project participation objects: Exceeds the maximum number of objects that can be passed to Salesforce at one time.");
    }

    foreach ($project_participations as $participation) {
      if ($participation->getSalesforceRecordId() != NULL) {
        throw new \Exception("Project Participation with project_participation_id: {$participation->getProjectParticipationId()} already has a Salesforce record Id.");
      }

      $sf_object = new \stdClass();
      $sf_object->Drupal_ID__c = $participation->getProjectParticipationId();
      $sf_object->Account__c = $participation->getAccountSfId();
      $sf_object->Project__c = $participation->getCampaignSfId();
      $sf_object->Type__c = "Organizer";

      $sf_objects[] = $sf_object;
    }

    if (!empty($sf_objects)) {
      try {
        $results = $this->client->upsert('Drupal_ID__c', $sf_objects, self::OBJECT_TYPE_PROJECT_PARTICIPATION);
        foreach ($results as $index => $result) {
          if (!empty($result->success)) {
            db_update('ioby_sf_project_participations')
              ->fields(
                array(
                  'salesforce_record_id' => $result->id,
                )
              )
              ->condition('project_participation_id', $project_participations[$index]->getProjectParticipationId())
              ->execute();
          }
          else {
            watchdog('ioby_sf', 'Received an error from Salesforce when trying to upsert project participation with project_participation_id: @project_participation_id. Errors: @errors',
              array('@project_participation_id' => $project_participations[$index]->getProjectParticipationId(), '@errors' => print_r($result->errors, TRUE)), WATCHDOG_ERROR);
          }
        }
      }
      catch (\Exception $e) {
        throw new \Exception("There was a problem pushing project participation objects to Salesforce.", 0, $e);
      }
    }
  }

  /**
   * @param CampaignMember[] $campaign_members
   *
   * @throws \InvalidArgumentException
   * @throws \Exception
   */
  function pushCampaignMemberObjects(array $campaign_members) {
    if (!$this->isConnected) {
      $this->connect();
    }

    $sf_objects = array();

    if (count($campaign_members) > self::SALESFORCE_API_LIMIT) {
      throw new \InvalidArgumentException("Error pushing campaign member objects: Exceeds the maximum number of objects that can be passed to Salesforce at one time.");
    }

    foreach ($campaign_members as $campaign_member) {
      $sf_object = new \stdClass();

      $sf_object->Drupal_ID__c = $campaign_member->getCampaignMemberId();

      if ($campaign_member->getSalesforceRecordId() == NULL) {
        $sf_object->ContactId = $campaign_member->getContactSfId();
        $sf_object->CampaignId = $campaign_member->getCampaignSfId();
        $sf_object->Status = 'Current';
        $sf_create_objects[] = $sf_object;
      }

      if ($campaign_member->getSalesforceRecordId() == NULL || ($campaign_member->getSalesforceRecordId() != NULL && $campaign_member->getNeedsUpdate())) {
        $sf_object->Donor__c = $campaign_member->getProjectDonor();
        $sf_object->Project_Leader__c = $campaign_member->getProjectLeader();
      }

      $sf_objects[] = $sf_object;
    }

    if (!empty($sf_objects)) {
      try {
        $results = $this->client->upsert('Drupal_ID__c', $sf_objects, self::OBJECT_TYPE_CAMPAIGN_MEMBER);
        foreach ($results as $index => $result) {
          if (!empty($result->success)) {
            db_update('ioby_sf_campaign_members')
              ->fields(
                array(
                  'salesforce_record_id' => $result->id,
                  'needs_update' => intval(FALSE),
                )
              )
              ->condition('campaign_member_id', $campaign_members[$index]->getCampaignMemberId())
              ->execute();
          }
          else {
            watchdog('ioby_sf', 'Received an error from Salesforce when trying to upsert campaign member with campaign_member_id: @campaign_member_id. Errors: @errors',
              array('@campaign_member_id' => $campaign_members[$index]->getCampaignMemberId(), '@errors' => print_r($result->errors, TRUE)), WATCHDOG_ERROR);
          }
        }
      }
      catch (\Exception $e) {
        throw new \Exception("There was a problem pushing campaign member objects (create) to Salesforce.", 0, $e);
      }
    }
  }

  /**
   * @param Opportunity[] $opportunities
   *
   * @throws \InvalidArgumentException
   * @throws \Exception
   */
  function pushOpportunityObjects(array $opportunities) {
    if (!$this->isConnected) {
      $this->connect();
    }

    // Make sure that the opportunity is linked to an account if possible
    $this->ensureAccountForOpportunityContacts($opportunities);

    $sf_objects = array();

    if (count($opportunities) > self::SALESFORCE_API_LIMIT) {
      throw new \InvalidArgumentException("Error pushing opportunity objects: Exceeds the maximum number of objects that can be passed to Salesforce at one time.");
    }

    foreach ($opportunities as $opportunity) {
      if ($opportunity->getSalesforceRecordId() != NULL) {
        throw new \Exception("Opportunity with opportunity_id: {$opportunity->getOpportunityId()} already has a Salesforce record Id.");
      }

      $sf_object = new \stdClass();

      $sf_object->Drupal_ID__c = $opportunity->getOpportunityId();

      $name = $opportunity->getName(120);

      $sf_object->Name = !empty($name) ? $name :  '$' . $opportunity->getAmount() . ' - ' . $opportunity->getOpportunityType();
      $sf_object->Drupal_Name__c = !empty($name) ? $opportunity->getName() :  '$' . $opportunity->getAmount() . ' - ' . $opportunity->getOpportunityType();
      $sf_object->Description = $opportunity->getDescription();
      $sf_object->RecordTypeId = self::RECORD_TYPE_OPPORTUNITY;
      $sf_object->Type = $opportunity->getOpportunityType();
      $sf_object->StageName = $opportunity->getStage();
      $sf_object->CloseDate = format_date($opportunity->getClosedDate(), 'custom', DATE_FORMAT_ISO);
      $sf_object->Amount = $opportunity->getAmount();
      $sf_object->Project__c = $opportunity->getCampaignSfId();
      $sf_object->AccountId = $opportunity->getAccountSfId();
      $sf_object->Fund__c = $opportunity->getFundSfId();

      if ($opportunity->getOpportunityType() == Opportunity::TYPE_DONATION) {
        $sf_object->npe01__Contact_Id_for_Role__c = $opportunity->getContactSfId();
      }

      if ($opportunity->getOrderId() != NULL) {
        $sf_object->Drupal_Order_ID__c = $opportunity->getOrderId();
      }

      if ($opportunity->getLineItemId() != NULL) {
        $sf_object->Line_Item_ID__c = $opportunity->getLineItemId();
      }

      $sf_objects[] = $sf_object;
    }

    if (!empty($sf_objects)) {
      try {
        $results = $this->client->upsert('Drupal_ID__c', $sf_objects, self::OBJECT_TYPE_OPPORTUNITY);
        foreach ($results as $index => $result) {
          if (!empty($result->success)) {
            // Flag for Order Line Item creation, but only if it's a website
            // donation. No manual donations.
            if ($opportunities[$index]->getOrderId() != NULL) {
              $this->flagLineItemForPush($opportunities, $index, $result);
            }

            db_update('ioby_sf_opportunities')
              ->fields(
                array(
                  'salesforce_record_id' => $result->id,
                  'needs_update' => intval(FALSE),
                )
              )
              ->condition('opportunity_id', $opportunities[$index]->getOpportunityId())
              ->execute();
          }
          else {
            watchdog('ioby_sf', 'Received an error from Salesforce when trying to create opportunity with opportunity_id: @opportunity_id. Errors: @errors',
              array('@opportunity_id' => $opportunities[$index]->getOpportunityId(), '@errors' => print_r($result->errors, TRUE)), WATCHDOG_ERROR);
          }
        }
      }
      catch (\Exception $e) {
        throw new \Exception("There was a problem pushing opportunity objects to Salesforce.", 0, $e);
      }
    }
  }

  /**
  /**
   * Once a line item (Opportunity in SF) has been successfully pushed, it is
   * ready to begin a second life as an OrderLineItem.
   *
   * @param array $opportunities
   *    The Opportunities that have been successfully pushed.
   * @param $index
   *    The Opportunity we are currently processing.
   * @param $result
   *    The result object from SF of the Opportunity we're processing.
   * @throws \Exception
   */
  protected function flagLineItemForPush(array $opportunities, $index, $result) {
    // Check the opportunity type. Fund Donations aren't tracked as part of the
    // Order / OrderLineItem rollup.
    $opportunity_type = $opportunities[$index]->getOpportunityType();
    if ($opportunity_type != 'Fund Donation') {
      try {
        db_insert('ioby_sf_order_line_items')
          ->fields(
            array(
              'commerce_order_id' => $opportunities[$index]->getOrderId(),
              'line_item_id' => $opportunities[$index]->getLineItemId(), // In case we need it.
              'amount' => $opportunities[$index]->getAmount(),
              'opportunity_type' => $opportunities[$index]->getOpportunityType(),
              'created' => $opportunities[$index]->getCreated(),
              'opportunity_salesforce_record_id' => $result->id,  // Opportunity__c
              'needs_update' => intval(TRUE),
            )
          )
          ->execute();
      }
      catch (\Exception $e) {
        throw new \Exception("Could not insert this Opportunity into the transitional line items table.", 0, $e);
      }
    }
  }

  /**
   * Once a line item has been successfully created as an Order Line Item in
   * Salesforce, mark it as such in the transitional table.
   *
   * @param array $order_line_items
   *    The OrderLineItems that are being pushed.
   * @param $index
   *    The index of the line item we're processing.
   * @param $result
   *    The result object from Salesforce.
   */
  protected function markLineItemAsPushed(array $order_line_items, $index, $result) {
    $line_item_id = $order_line_items[$index]->getLineItemId();
    db_update('ioby_sf_order_line_items')
      ->fields(array(
          'needs_update' => intval(FALSE),
          'salesforce_record_id' => $result->id
        )
      )
      ->condition('line_item_id', $line_item_id)
      ->execute();
  }

  /**
   * Pushes Orders (which represent Commerce orders) to SF.
   *
   * @param array $orders
   *    An array of Order object to push.
   * @throws \Exception
   */
  public function pushOrders(array $orders) {
    if (!$this->isConnected) {
      $this->connect();
    }

    if (count($orders) > self::SALESFORCE_API_LIMIT) {
      throw new \InvalidArgumentException("Error pushing order objects: Exceeds the maximum number of objects that can be passed to Salesforce at one time.");
    }

    foreach ($orders as $order) {
      if ($order->getSalesforceRecordId() != NULL) {
        throw new \Exception("Order with Commerce order_id: {$order->getCommerceOrderId()} already has a Salesforce record Id.");
      }

      $sf_object = new \stdClass();

      $sf_object->Drupal_Order_ID__c = $order->getCommerceOrderId();
      $sf_object->Order_Date__c = format_date($order->getCreated(), 'custom', DATE_FORMAT_ISO);
      $sf_objects[] = $sf_object;
    }

    try {
      $results = $this->client->upsert('Drupal_Order_ID__c', $sf_objects, self::OBJECT_TYPE_ORDER);
      foreach ($results as $index => $result) {
        if (!empty($result->success)) {
          // Update our transitional table.
          $this->storeCommerceOrder($orders, $index, $result);
        }
        else {
          watchdog('ioby_sf', 'Received an error from Salesforce when trying to upsert an Order with commerce_order_id: @commerce_order_id. Errors: @errors',
            array('@commerce_order_id' => $orders[$index]->getCommerceOrderId(), '@errors' => print_r($result->errors, TRUE)), WATCHDOG_ERROR);
        }
      }
    }
    catch (\Exception $e) {
      throw new \Exception("There was a problem pushing order objects to Salesforce.", 0, $e);
    }
  }

  /**
   * Once a Commerce order has been pushed to SF and created as an Order, saves
   * the Commerce order id and the Salesforce ID so that we can reference it
   * later.
   *
   * @param array $orders
   *    The array of Orders from this sync.
   * @param $index
   *    The index of the Order we're current processing.
   * @param $result
   *    The result object from Salesforce.
   *
   * @see $this->getCommerceItemId()
   *
   * @throws \Exception
   */
  protected function storeCommerceOrder(array $orders, $index, $result) {
    try {
      db_merge('ioby_sf_commerce_orders')
        ->fields(array(
          'commerce_order_id' => $orders[$index]->getCommerceOrderId(),
          'salesforce_record_id' => $result->id,
        ))
        ->key(array(
          'commerce_order_id' => $orders[$index]->getCommerceOrderId(),
        ))
        ->execute();
    }
    catch (\Exception $e) {
      throw new \Exception('There was a problem inserting an Order into the transitional table.', 0, $e);
    }
  }
  /**
   * Pushes line items up to Salesforce as Order Line Items.
   *
   * @param array $order_line_items
   *    An array of OrderLineItem objects to push.
   * @throws \Exception
   */
  public function pushOrderLineItems(array $order_line_items) {
    if (!$this->isConnected) {
      $this->connect();
    }

    if (count($order_line_items) > self::SALESFORCE_API_LIMIT) {
      throw new \InvalidArgumentException("Error pushing order line item objects: Exceeds the maximum number of objects that can be passed to Salesforce at one time.");
    }

    $sf_objects = array();
    foreach ($order_line_items as $order_line_item) {
      if ($order_line_item->getSalesforceRecordId() != NULL) {
        throw new \Exception("Line item with order_id: {$order_line_item->getLineItemId()} already has a Salesforce record Id.");
      }

      $sf_object = new \stdClass();

      if ($commerce_order_id =  $this->getCommerceItemId($order_line_item)) {
        $sf_object->Opportunity__c = $order_line_item->getOpportunitySalesforceRecordId(); // Salesforce ID for the Opportunity.
        $sf_object->Order__c = $commerce_order_id; // Salesforce ID for the Commerce order.
        $sf_object->Opportunity_Amoount__c = $order_line_item->getAmount();
        $sf_object->Opportunity_Type__c = $order_line_item->getOpportunityType();

        $sf_objects[] = $sf_object;
      }
    }

    try {
      $results = $this->client->create($sf_objects, self::OBJECT_TYPE_ORDER_LINE_ITEM);
      foreach ($results as $index => $result) {
        if (!empty($result->success)) {
          $this->markLineItemAsPushed($order_line_items, $index, $result);
        }
        else {
          watchdog('ioby_sf', 'Received an error from Salesforce when trying to create an order line item for line item: @line_item_id. Errors: @errors',
            array('@line_item_id' => $order_line_items[$index]->getLineItemId(), '@errors' => print_r($result->errors, TRUE)), WATCHDOG_ERROR);
        }
      }
    }
    catch (\Exception $e) {
      throw new \Exception("There was a problem pushing order line items to Salesforce.", 0, $e);
    }
  }

  /**
   * Gets the Salesforce ID for the Commerce order this line item belongs to.
   *
   * @param $order_line_item
   *    The OrderLineItem.
   * @return string|bool
   *    The Salesforce ID of the Commerce order, or FALSE.
   */
  protected function getCommerceItemId($order_line_item) {
    if ($commerce_order_id = $order_line_item->getCommerceOrderId()) {
      return db_select('ioby_sf_commerce_orders', 'c')
        ->fields('c', array('salesforce_record_id'))
        ->condition('c.commerce_order_id', $commerce_order_id)
        ->execute()
        ->fetchField();
    }

    return FALSE;
  }
  /**
   * @param Opportunity[] $opportunities
   *
   * @throws \InvalidArgumentException
   */
  public function pushManualOpportunityUpdates(array $opportunities) {
    if (!$this->isConnected) {
      $this->connect();
    }

    $sf_objects = array();

    if (count($opportunities) > self::SALESFORCE_API_LIMIT) {
      throw new \InvalidArgumentException("Error pushing manual opportunity updates: Exceeds the maximum number of objects that can be passed to Salesforce at one time.");
    }

    foreach ($opportunities as $opportunity) {
      if (!$opportunity->getNeedsUpdate()) {
        throw new \Exception("Opportunity with opportunity_id: {$opportunity->getOpportunityId()} is not flagged for update.");
      }

      $sf_object = new \stdClass();
      $sf_object->Drupal_ID__c = $opportunity->getOpportunityId();
      $sf_object->Id = $opportunity->getSalesforceRecordId();

      $sf_objects[] = $sf_object;
    }

    if (!empty($sf_objects)) {
      try {
        $results = $this->client->update($sf_objects, self::OBJECT_TYPE_OPPORTUNITY);
        foreach ($results as $index => $result) {
          if (!empty($result->success)) {
            db_update('ioby_sf_opportunities')
              ->fields(
                array(
                  'needs_update' => intval(FALSE),
                )
              )
              ->condition('opportunity_id', $opportunities[$index]->getOpportunityId())
              ->execute();
          }
          else {
            watchdog('ioby_sf', 'Received an error from Salesforce when trying to update manual opportunity with opportunity_id: @opportunity_id. Errors: @errors',
              array('@opportunity_id' => $opportunities[$index]->getOpportunityId(), '@errors' => print_r($result->errors, TRUE)), WATCHDOG_ERROR);
          }
        }
      }
      catch (\Exception $e) {
        throw new \Exception("There was a problem pushing manual opportunity update to Salesforce.", 0, $e);
      }
    }
  }

  /**
   * Updates Potential Projects in Salesforce once they've been assigned to a
   * Project node in Drupal. The update adds the nid to the Potential Project
   * so that we can perform upserts later when pushing Campaigns.
   *
   * @param array $potential_projects
   *    An array of potential projects that contain a nid and SF record id.
   *
   * @author Paul Venuti
   */
  public function pushPotentialProjects(array $potential_projects) {
    if (!$this->isConnected) {
      $this->connect();
    }

    $sf_objects = array();

    if (count($potential_projects) > self::SALESFORCE_API_LIMIT) {
      throw new \InvalidArgumentException("Error pushing potential project updates: Exceeds the maximum number of objects that can be passed to Salesforce at one time.");
    }

    foreach ($potential_projects as $project) {
      $sf_object = new \stdClass();

      $sf_object->Node_ID__c = $project->nid;
      $sf_object->Id = $project->salesforce_record_id;

      $sf_objects[] = $sf_object;
    }

    if (!empty($sf_objects)) {
      try {
        $results = $this->client->update($sf_objects, self::OBJECT_TYPE_CAMPAIGN);
        foreach ($results as $index => $result) {
          if (!empty($result->success)) {
            // Update the potential_projects table so we don't push this again.
            db_update('ioby_sf_potential_projects')
              ->fields(array(
                  'create_new_sf_object' => intval(FALSE),
                ))
              ->condition('nid', $potential_projects[$index]->nid)
              ->execute();
          }
          else {
            // Log an error if any updates failed.
            watchdog('ioby_sf', 'Received an error from Salesforce when trying to update the potential project with salesforce_record_id: @record_id, nid: @nid. Errors: @errors',
              array('@record_id' => $potential_projects[$index]->salesforce_record_id, '@errors' => print_r($result->errors, TRUE), '@nid' => $potential_projects[$index]->nid), WATCHDOG_ERROR);
          }
        }
      }
      catch (\Exception $e) {
        throw new \Exception("There was a problem updating potential projects in Salesforce.", 0, $e);
      }
    }
  }

  public function pullNewManualOpportunities() {
    if (!$this->isConnected) {
      $this->connect();
    }

    $repository = new SalesforceRepository();

    $query = "SELECT Id, Amount, Name, Description, CloseDate, Project__c, ";
    $query .= "Type, Fund__c, npe01__Contact_Id_for_Role__c, CreatedDate, ";
    $query .= "LastModifiedDate, StageName ";
    $query .= "FROM Opportunity o ";
    $query .= "WHERE (o.Type = '%s' OR o.Type = '%s') AND o.IsWON = true ";
    $query .= "AND o.Drupal_ID__c = null AND o.Project__c <> null";

    $result = $this->client->query(sprintf($query, Opportunity::TYPE_DONATION, Opportunity::TYPE_MATCH));

    foreach ($result->records as $record) {
      $insert_fields = array(
        'salesforce_record_id' => $record->Id,
        'created' => strtotime($record->CreatedDate),
        'changed' => strtotime($record->LastModifiedDate),
        'closed_date' => strtotime($record->CloseDate),
        'amount' => $record->Amount,
        'name' => $record->Name,
        'opportunity_type' => $record->Type,
        'entry_type' => 'manual-salesforce'
      );

      if (isset($record->Description)) {
        $insert_fields['description'] = $record->Description;
      }
      if (isset($record->Project__c)) {
        $insert_fields['project_nid'] = $repository->getProjectNidById($record->Project__c);
      }
      if (isset($record->Fund__c)) {
        $insert_fields['campaign_nid'] = $repository->getCampaignNidById($record->Fund__c);
      }
      if (isset($record->npe01__Contact_Id_for_Role__c)) {
        $insert_fields['contact_email'] = $repository->getContactEmailById($record->npe01__Contact_Id_for_Role__c);
      }
      if (isset($record->StageName)) {
        $insert_fields['stage'] = $record->StageName;
      }

      try {
        db_merge('ioby_sf_opportunities')
          ->insertFields($insert_fields)
          ->key(array('salesforce_record_id' => $record->Id))
          ->execute();
      }
      catch (\Exception $e) {
        throw new \Exception("There was a problem inserting the Manual Opportunity record from Salesforce.", 0, $e);
      }
    }
  }

  /**
   * Pulls records of type Potential Project which are active and stores them
   * in the potential_projects table.
   *
   * @author Paul Venuti
   */
  public function pullPotentialProjects() {
    if (!$this->isConnected) {
      $this->connect();
    }

    $repository = new SalesforceRepository();

    $query = "SELECT Id, Name ";
    $query .= "FROM Campaign c ";
    $query .= "WHERE c.RecordTypeId = '%s' AND c.isActive = true";

    if ($potential_projects = $this->client->query(sprintf($query, self::RECORD_TYPE_POTENTIAL_PROJECT))) {
      // Merge them into the table (some might already exist).
      $record_ids = array();
      foreach ($potential_projects as $potential_project) {
        $record_ids[] = $potential_project->Id;
        try {
          db_merge('ioby_sf_potential_projects')
            ->fields(array(
                'title' => $potential_project->Name,
                'salesforce_record_id' => $potential_project->Id,
                'connected_to_sf' => TRUE,
              ))
            ->key(array(
                'salesforce_record_id' => $potential_project->Id,
              ))
            ->execute();
        }
        catch (\Exception $e) {
          throw new \Exception("There was a problem inserting the Potential Project record from Salesforce.", 0, $e);
        }
      }
      // Remove outdated entries.
      if (count($record_ids) > 0) {
        $this->removeInactivePotentialProjects($record_ids);
      }
    }
  }

  /**
   * Removes Potential Projects that are inactive or which no longer exist
   * (probably because they were converted to full Projects).
   *
   * @author Paul Venuti
   */
  private function removeInactivePotentialProjects($record_ids) {
    $deleted_rows = db_delete('ioby_sf_potential_projects')
                  ->condition('salesforce_record_id', $record_ids, 'NOT IN')
                  ->execute();
    if ($deleted_rows > 0) {
      $args = array(
        '@rows' => $deleted_rows,
      );
      watchdog('ioby_sf', 'Removed @rows rows from the potential projects table', $args, WATCHDOG_DEBUG);
    }
  }

  /**
   * @param string $salesforce_record_id
   *
   * @return bool
   *    A boolean indicating if a campaign with the given ID exists in Salesforce.
   */
  public function campaignExists($salesforce_record_id) {
    if (!$this->isConnected) {
      $this->connect();
    }

    try {
      $result = $this->client->query(sprintf("SELECT Id FROM Campaign WHERE Id = '%s'", $salesforce_record_id));

      if (count($result->records) > 0) {
        return TRUE;
      }
    }
    catch (\Exception $e) {
      watchdog_exception('ioby_sf', $e, 'Received an error from Salesforce when trying to query for campaign with Id: @Id.',
        array('@Id' => $salesforce_record_id, WATCHDOG_ERROR));
    }

    return FALSE;
  }

  private function connect() {
    $this->client = new \SforceEnterpriseClient();
    $this->client->createConnection($this->wsdl);

    try {
      $this->client->login($this->username, $this->password);
    }
    catch (\Exception $e) {
      throw new \Exception("There was a problem logging in to Salesforce.", 0, $e);
    }

    $this->isConnected = TRUE;
  }

  /**
   * Since not all Contacts in Drupal have associated contact records and
   * Opportunity records in SF require an Account, we need to look up the automatically
   * created Account Id for the Contact if the Contact doesn't already have one in our
   * tables and add it to the opportunity object to be pushed to SF.
   *
   * @param Opportunity[] $opportunities
   */
  private function ensureAccountForOpportunityContacts(array &$opportunities) {
    $contact_account_map = array();

    foreach ($opportunities as $opportunity) {
		
		if(!isset($opportunity))
		{
			continue;
		}
		
      if ($opportunity->getContactSfId() != NULL && $opportunity->getAccountSfId() == NULL && $opportunity->getOpportunityType() != Opportunity::TYPE_COUPON && $opportunity->getOpportunityType() != Opportunity::TYPE_MATCH) {

		  
		  		$contact_account_map[$opportunity->getContactSfId()] = NULL;
		  
      }
    }

    try {
      if (!empty($contact_account_map)) {
        $accounts = $this->client->retrieve('AccountId', 'Contact', array_keys($contact_account_map));
        if (!empty($accounts)) {
          foreach ($accounts as $account) {
			  
		  		$contact_account_map[$account->Id] = $account->AccountId;
		  }
        }

        foreach ($opportunities as &$opportunity) {
			
			if(!isset($opportunity))
			{
				continue;
			}			
			
          if ($opportunity->getAccountSfId() == NULL && $opportunity->getContactSfId() != NULL && $contact_account_map[$opportunity->getContactSfId()] != NULL) {
		
			  $opportunity->setAccountSfId($contact_account_map[$opportunity->getContactSfId()]);
			  
          }
        }
      }
    }
    catch (\Exception $e) {
      throw new \Exception("There was a problem retrieving AccountIds for contacts from Salesforce.", 0, $e);
    }
  }
}
