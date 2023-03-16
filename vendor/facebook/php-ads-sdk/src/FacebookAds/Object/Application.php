<?php
/**
 * Copyright (c) 2015-present, Facebook, Inc. All rights reserved.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */

namespace FacebookAds\Object;

use FacebookAds\ApiRequest;
use FacebookAds\Cursor;
use FacebookAds\Http\RequestInterface;
use FacebookAds\TypeChecker;
use FacebookAds\Object\Fields\ApplicationFields;
use FacebookAds\Object\Values\AdNetworkAnalyticsSyncQueryResultAggregationPeriodValues;
use FacebookAds\Object\Values\AdNetworkAnalyticsSyncQueryResultBreakdownsValues;
use FacebookAds\Object\Values\AdNetworkAnalyticsSyncQueryResultMetricsValues;
use FacebookAds\Object\Values\AdNetworkAnalyticsSyncQueryResultOrderingColumnValues;
use FacebookAds\Object\Values\AdNetworkAnalyticsSyncQueryResultOrderingTypeValues;
use FacebookAds\Object\Values\ApplicationAnPlatformsValues;
use FacebookAds\Object\Values\ApplicationMutationMethodValues;
use FacebookAds\Object\Values\ApplicationPlatformValues;
use FacebookAds\Object\Values\ApplicationPostMethodValues;
use FacebookAds\Object\Values\ApplicationRequestTypeValues;
use FacebookAds\Object\Values\ApplicationRoleValues;
use FacebookAds\Object\Values\ApplicationScoreTypeValues;
use FacebookAds\Object\Values\ApplicationSortOrderValues;
use FacebookAds\Object\Values\ApplicationSupportedPlatformsValues;
use FacebookAds\Object\Values\EventTypeValues;
use FacebookAds\Object\Values\PhotoBackdatedTimeGranularityValues;
use FacebookAds\Object\Values\PhotoUnpublishedContentTypeValues;
use FacebookAds\Object\Values\ProfilePictureSourceTypeValues;

/**
 * This class is auto-generated.
 *
 * For any issues or feature requests related to this class, please let us know
 * on github and we'll fix in our codegen framework. We'll not be able to accept
 * pull request for this class.
 *
 */

class Application extends AbstractCrudObject {

  /**
   * @return ApplicationFields
   */
  public static function getFieldsEnum() {
    return ApplicationFields::getInstance();
  }

  protected static function getReferencedEnums() {
    $ref_enums = array();
    $ref_enums['SupportedPlatforms'] = ApplicationSupportedPlatformsValues::getInstance()->getValues();
    $ref_enums['AnPlatforms'] = ApplicationAnPlatformsValues::getInstance()->getValues();
    $ref_enums['Platform'] = ApplicationPlatformValues::getInstance()->getValues();
    $ref_enums['RequestType'] = ApplicationRequestTypeValues::getInstance()->getValues();
    $ref_enums['MutationMethod'] = ApplicationMutationMethodValues::getInstance()->getValues();
    $ref_enums['PostMethod'] = ApplicationPostMethodValues::getInstance()->getValues();
    $ref_enums['ScoreType'] = ApplicationScoreTypeValues::getInstance()->getValues();
    $ref_enums['SortOrder'] = ApplicationSortOrderValues::getInstance()->getValues();
    $ref_enums['Role'] = ApplicationRoleValues::getInstance()->getValues();
    return $ref_enums;
  }


  public function deleteAccounts(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'type' => 'type_enum',
      'uid' => 'int',
    );
    $enums = array(
      'type_enum' => array(
        'test-users',
      ),
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_DELETE,
      '/accounts',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getAccounts(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'type' => 'type_enum',
    );
    $enums = array(
      'type_enum' => array(
        'test-users',
      ),
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/accounts',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createAccount(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'installed' => 'bool',
      'minor' => 'bool',
      'name' => 'string',
      'owner_access_token' => 'string',
      'permissions' => 'list<Permission>',
      'type' => 'type_enum',
      'uid' => 'int',
    );
    $enums = array(
      'type_enum' => array(
        'test-users',
      ),
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/accounts',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getAchievements(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'achievement' => 'Object',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/achievements',
      new OpenGraphObject(),
      'EDGE',
      OpenGraphObject::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createActivity(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'advertiser_id' => 'string',
      'advertiser_tracking_enabled' => 'bool',
      'anon_id' => 'string',
      'app_user_id' => 'string',
      'application_tracking_enabled' => 'bool',
      'attribution' => 'string',
      'auto_publish' => 'bool',
      'bundle_id' => 'string',
      'bundle_short_version' => 'string',
      'bundle_version' => 'string',
      'consider_views' => 'bool',
      'custom_events' => 'list<Object>',
      'custom_events_file' => 'file',
      'device_token' => 'string',
      'event' => 'event_enum',
      'extinfo' => 'Object',
      'include_dwell_data' => 'bool',
      'include_video_data' => 'bool',
      'installer_package' => 'string',
      'migration_bundle' => 'string',
      'page_id' => 'unsigned int',
      'page_scoped_user_id' => 'unsigned int',
      'receipt_data' => 'string',
      'ud' => 'map',
      'url_schemes' => 'list<string>',
      'user_id' => 'string',
      'user_id_type' => 'user_id_type_enum',
      'windows_attribution_id' => 'string',
    );
    $enums = array(
      'event_enum' => array(
        'CUSTOM_APP_EVENTS',
        'DEFERRED_APP_LINK',
        'MOBILE_APP_INSTALL',
      ),
      'user_id_type_enum' => array(
        'INSTANT_GAMES_PLAYER_ID',
      ),
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/activities',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getAdNetworkAnalytics(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'aggregation_period' => 'aggregation_period_enum',
      'breakdowns' => 'list<breakdowns_enum>',
      'filters' => 'list<map>',
      'limit' => 'unsigned int',
      'metrics' => 'list<metrics_enum>',
      'ordering_column' => 'ordering_column_enum',
      'ordering_type' => 'ordering_type_enum',
      'since' => 'datetime',
      'until' => 'datetime',
    );
    $enums = array(
      'aggregation_period_enum' => AdNetworkAnalyticsSyncQueryResultAggregationPeriodValues::getInstance()->getValues(),
      'breakdowns_enum' => AdNetworkAnalyticsSyncQueryResultBreakdownsValues::getInstance()->getValues(),
      'metrics_enum' => AdNetworkAnalyticsSyncQueryResultMetricsValues::getInstance()->getValues(),
      'ordering_column_enum' => AdNetworkAnalyticsSyncQueryResultOrderingColumnValues::getInstance()->getValues(),
      'ordering_type_enum' => AdNetworkAnalyticsSyncQueryResultOrderingTypeValues::getInstance()->getValues(),
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/adnetworkanalytics',
      new AdNetworkAnalyticsSyncQueryResult(),
      'EDGE',
      AdNetworkAnalyticsSyncQueryResult::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createAdNetworkAnalytic(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'aggregation_period' => 'aggregation_period_enum',
      'breakdowns' => 'list<breakdowns_enum>',
      'filters' => 'list<Object>',
      'limit' => 'int',
      'metrics' => 'list<metrics_enum>',
      'ordering_column' => 'ordering_column_enum',
      'ordering_type' => 'ordering_type_enum',
      'since' => 'datetime',
      'until' => 'datetime',
    );
    $enums = array(
      'aggregation_period_enum' => AdNetworkAnalyticsSyncQueryResultAggregationPeriodValues::getInstance()->getValues(),
      'breakdowns_enum' => AdNetworkAnalyticsSyncQueryResultBreakdownsValues::getInstance()->getValues(),
      'metrics_enum' => AdNetworkAnalyticsSyncQueryResultMetricsValues::getInstance()->getValues(),
      'ordering_column_enum' => AdNetworkAnalyticsSyncQueryResultOrderingColumnValues::getInstance()->getValues(),
      'ordering_type_enum' => AdNetworkAnalyticsSyncQueryResultOrderingTypeValues::getInstance()->getValues(),
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/adnetworkanalytics',
      new Application(),
      'EDGE',
      Application::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getAdNetworkAnalyticsResults(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'query_ids' => 'list<string>',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/adnetworkanalytics_results',
      new AdNetworkAnalyticsAsyncQueryResult(),
      'EDGE',
      AdNetworkAnalyticsAsyncQueryResult::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getAgencies(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/agencies',
      new Business(),
      'EDGE',
      Business::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getAndroidDialogConfigs(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/android_dialog_configs',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getAppEventTypes(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/app_event_types',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createAppIndexing(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'app_version' => 'string',
      'device_session_id' => 'string',
      'extra_info' => 'string',
      'platform' => 'platform_enum',
      'request_type' => 'request_type_enum',
      'tree' => 'map',
    );
    $enums = array(
      'platform_enum' => ApplicationPlatformValues::getInstance()->getValues(),
      'request_type_enum' => ApplicationRequestTypeValues::getInstance()->getValues(),
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/app_indexing',
      new Application(),
      'EDGE',
      Application::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createAppIndexingSession(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'device_session_id' => 'string',
      'extinfo' => 'string',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/app_indexing_session',
      new Application(),
      'EDGE',
      Application::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getAppInsights(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'aggregateBy' => 'aggregateBy_enum',
      'breakdowns' => 'list<string>',
      'ecosystem' => 'ecosystem_enum',
      'event_name' => 'string',
      'intervals_to_aggregate' => 'int',
      'metric_key' => 'string',
      'period' => 'period_enum',
      'since' => 'datetime',
      'until' => 'datetime',
    );
    $enums = array(
      'aggregateBy_enum' => array(
        'AVERAGE_JOURNEY_LENGTH',
        'CONVERTED_JOURNEY_PERCENT',
        'COUNT',
        'COUNT_IDENTIFIED_USERS',
        'COUNT_PER_USER',
        'DAU',
        'EVENT_SOURCE_IDS',
        'JOURNEY_CHANNEL_INCLUSION',
        'JOURNEY_INCLUSION',
        'MAU',
        'MEDIAN_JOURNEY_LENGTH',
        'MEDIAN_VALUE',
        'MEDIAN_VALUE_PER_USER',
        'OVERLAP',
        'PERCENTILES_COUNT',
        'PERCENTILES_USD_VALUE',
        'PERCENTILES_VALUE',
        'SCORE',
        'SESSIONS_PER_JOURNEY',
        'SESSION_BOUNCE_RATE',
        'SUM',
        'SUM_IDENTIFIED_USERS',
        'SUM_PER_EVENT',
        'TOPK',
        'UNKNOWN_USERS',
        'USD_SUM',
        'USD_SUM_IDENTIFIED_USERS',
        'USD_SUM_PER_EVENT',
        'USD_SUM_PER_USER',
        'USD_VALUE_PER_USER',
        'USERS',
        'USER_PROPERTY_USER_COUNT',
        'VALUE_PER_USER',
        'WAU',
      ),
      'ecosystem_enum' => array(
        'GAME',
        'NON_GAME',
      ),
      'period_enum' => array(
        'daily',
        'days_28',
        'hourly',
        'lifetime',
        'mins_15',
        'monthly',
        'range',
        'weekly',
      ),
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/app_insights',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getAppInstalledGroups(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'group_id' => 'string',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/app_installed_groups',
      new Group(),
      'EDGE',
      Group::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createAppPushDeviceToken(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'device_id' => 'string',
      'device_token' => 'string',
      'platform' => 'platform_enum',
    );
    $enums = array(
      'platform_enum' => ApplicationPlatformValues::getInstance()->getValues(),
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/app_push_device_token',
      new Application(),
      'EDGE',
      Application::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getAppAssets(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/appassets',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createAsset(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'asset' => 'file',
      'comment' => 'string',
      'type' => 'string',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/assets',
      new Application(),
      'EDGE',
      Application::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getAudiences(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'ad_account' => 'string',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/audiences',
      new CustomAudience(),
      'EDGE',
      CustomAudience::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function deleteAuthorizedAdAccounts(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'account_id' => 'string',
      'business' => 'string',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_DELETE,
      '/authorized_adaccounts',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getAuthorizedAdAccounts(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'business' => 'string',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/authorized_adaccounts',
      new AdAccount(),
      'EDGE',
      AdAccount::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createAuthorizedAdAccount(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'account_id' => 'string',
      'business' => 'string',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/authorized_adaccounts',
      new Application(),
      'EDGE',
      Application::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function deleteBanned(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'uids' => 'list<int>',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_DELETE,
      '/banned',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getBanned(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'uid' => 'int',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/banned',
      new User(),
      'EDGE',
      User::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createBanned(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'uids' => 'list<int>',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/banned',
      new User(),
      'EDGE',
      User::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getButtonAutoDetectionDeviceSelection(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'device_id' => 'string',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/button_auto_detection_device_selection',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createButtonIndexing(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'app_version' => 'string',
      'device_id' => 'string',
      'extinfo' => 'string',
      'indexed_button_list' => 'list<map>',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/button_indexing',
      new Application(),
      'EDGE',
      Application::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createCodelessEventBinding(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'bindings' => 'list<map>',
      'mutation_method' => 'mutation_method_enum',
      'platform' => 'platform_enum',
    );
    $enums = array(
      'mutation_method_enum' => ApplicationMutationMethodValues::getInstance()->getValues(),
      'platform_enum' => ApplicationPlatformValues::getInstance()->getValues(),
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/codeless_event_bindings',
      new Application(),
      'EDGE',
      Application::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createCodelessEventMapping(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'mappings' => 'list<map>',
      'mutation_method' => 'mutation_method_enum',
      'platform' => 'platform_enum',
      'post_method' => 'post_method_enum',
    );
    $enums = array(
      'mutation_method_enum' => ApplicationMutationMethodValues::getInstance()->getValues(),
      'platform_enum' => ApplicationPlatformValues::getInstance()->getValues(),
      'post_method_enum' => ApplicationPostMethodValues::getInstance()->getValues(),
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/codeless_event_mappings',
      new Application(),
      'EDGE',
      Application::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getConnections(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/connections',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getCustomAudienceThirdPartyId(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'limit_event_usage' => 'bool',
      'udid' => 'string',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/custom_audience_third_party_id',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getDaChecks(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'checks' => 'list<string>',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/da_checks',
      new DACheck(),
      'EDGE',
      DACheck::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getDirectDeals(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/direct_deals',
      new DirectDeal(),
      'EDGE',
      DirectDeal::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getEvents(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'include_canceled' => 'bool',
      'type' => 'type_enum',
    );
    $enums = array(
      'type_enum' => EventTypeValues::getInstance()->getValues(),
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/events',
      new Event(),
      'EDGE',
      Event::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getFullAppIndexingInfos(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'app_version' => 'string',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/full_app_indexing_infos',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createFullAppIndexingInfo(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'app_version' => 'string',
      'full_app_indexing_info_classes' => 'list<map>',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/full_app_indexing_infos',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getInsightsEventLabels(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'add' => 'list<string>',
      'delete' => 'unsigned int',
      'ecosystem' => 'bool',
      'since' => 'datetime',
      'until' => 'datetime',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/insights_event_labels',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getIosDialogConfigs(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/ios_dialog_configs',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createLeaderboardsCreate(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'context_id' => 'string',
      'decimal_offset' => 'unsigned int',
      'name' => 'string',
      'score_type' => 'score_type_enum',
      'sort_order' => 'sort_order_enum',
      'unit' => 'string',
    );
    $enums = array(
      'score_type_enum' => ApplicationScoreTypeValues::getInstance()->getValues(),
      'sort_order_enum' => ApplicationSortOrderValues::getInstance()->getValues(),
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/leaderboards_create',
      new Application(),
      'EDGE',
      Application::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createLeaderboardsDeleteEntry(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'name' => 'string',
      'player_id' => 'string',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/leaderboards_delete_entry',
      new Application(),
      'EDGE',
      Application::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createLeaderboardsReset(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'name' => 'string',
      'reset_time' => 'datetime',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/leaderboards_reset',
      new Application(),
      'EDGE',
      Application::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createLeaderboardsSetScore(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'extra_data' => 'string',
      'name' => 'string',
      'player_id' => 'string',
      'score' => 'unsigned int',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/leaderboards_set_score',
      new Application(),
      'EDGE',
      Application::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createMachine(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/machines',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createMmpAuditing(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'advertiser_id' => 'string',
      'attribution' => 'string',
      'attribution_model' => 'string',
      'auditing_token' => 'string',
      'click_attr_window' => 'unsigned int',
      'custom_events' => 'list<Object>',
      'decline_reason' => 'string',
      'event' => 'string',
      'event_reported_time' => 'unsigned int',
      'fb_ad_id' => 'unsigned int',
      'fb_click_time' => 'unsigned int',
      'fb_view_time' => 'unsigned int',
      'is_fb' => 'bool',
      'view_attr_window' => 'unsigned int',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/mmp_auditing',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getMobileSdkGk(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'device_id' => 'string',
      'platform' => 'platform_enum',
      'sdk_version' => 'string',
    );
    $enums = array(
      'platform_enum' => array(
        'ANDROID',
        'IOS',
      ),
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/mobile_sdk_gk',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getMoodsForApplication(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/moods_for_application',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getObjectTypes(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/object_types',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getObjects(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'type' => 'Object',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/objects',
      new OpenGraphObject(),
      'EDGE',
      OpenGraphObject::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createObject(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'object' => 'Object',
      'type' => 'string',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/objects',
      new OpenGraphObject(),
      'EDGE',
      OpenGraphObject::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createOccludesPopup(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'flash' => 'bool',
      'unity' => 'bool',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/occludespopups',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getOzoneRelease(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/ozone_release',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createOzoneRelease(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'auto_push_to_prod' => 'bool',
      'changelog' => 'string',
      'channel_id' => 'unsigned int',
      'release_name' => 'string',
      'rollout_percentage' => 'float',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/ozone_release',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function deletePaymentCurrencies(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'currency_url' => 'string',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_DELETE,
      '/payment_currencies',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createPaymentCurrency(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'currency_url' => 'string',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/payment_currencies',
      new Application(),
      'EDGE',
      Application::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function deletePaymentsTestUsers(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'uids' => 'list<int>',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_DELETE,
      '/payments_test_users',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getPaymentsTestUsers(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/payments_test_users',
      new User(),
      'EDGE',
      User::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createPaymentsTestUser(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'uids' => 'list<int>',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/payments_test_users',
      new Application(),
      'EDGE',
      Application::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getPermissions(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'android_key_hash' => 'string',
      'ios_bundle_id' => 'string',
      'permission' => 'list<Permission>',
      'proxied_app_id' => 'int',
      'status' => 'list<status_enum>',
    );
    $enums = array(
      'status_enum' => array(
        'live',
        'unapproved',
      ),
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/permissions',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createPhoto(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'aid' => 'string',
      'allow_spherical_photo' => 'bool',
      'alt_text_custom' => 'string',
      'android_key_hash' => 'string',
      'application_id' => 'string',
      'attempt' => 'unsigned int',
      'audience_exp' => 'bool',
      'backdated_time' => 'datetime',
      'backdated_time_granularity' => 'backdated_time_granularity_enum',
      'caption' => 'string',
      'composer_session_id' => 'string',
      'direct_share_status' => 'unsigned int',
      'feed_targeting' => 'Object',
      'filter_type' => 'unsigned int',
      'full_res_is_coming_later' => 'bool',
      'initial_view_heading_override_degrees' => 'unsigned int',
      'initial_view_pitch_override_degrees' => 'unsigned int',
      'initial_view_vertical_fov_override_degrees' => 'unsigned int',
      'ios_bundle_id' => 'string',
      'is_explicit_location' => 'bool',
      'is_explicit_place' => 'bool',
      'manual_privacy' => 'bool',
      'message' => 'string',
      'name' => 'string',
      'no_story' => 'bool',
      'offline_id' => 'unsigned int',
      'og_action_type_id' => 'string',
      'og_icon_id' => 'string',
      'og_object_id' => 'string',
      'og_phrase' => 'string',
      'og_set_profile_badge' => 'bool',
      'og_suggestion_mechanism' => 'string',
      'place' => 'Object',
      'privacy' => 'string',
      'profile_id' => 'int',
      'proxied_app_id' => 'string',
      'published' => 'bool',
      'qn' => 'string',
      'scheduled_publish_time' => 'unsigned int',
      'spherical_metadata' => 'map',
      'sponsor_id' => 'string',
      'sponsor_relationship' => 'unsigned int',
      'tags' => 'list<Object>',
      'target_id' => 'int',
      'targeting' => 'Object',
      'time_since_original_post' => 'unsigned int',
      'uid' => 'int',
      'unpublished_content_type' => 'unpublished_content_type_enum',
      'url' => 'string',
      'user_selected_tags' => 'bool',
      'vault_image_id' => 'string',
    );
    $enums = array(
      'backdated_time_granularity_enum' => PhotoBackdatedTimeGranularityValues::getInstance()->getValues(),
      'unpublished_content_type_enum' => PhotoUnpublishedContentTypeValues::getInstance()->getValues(),
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/photos',
      new Photo(),
      'EDGE',
      Photo::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getPicture(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'redirect' => 'bool',
      'type' => 'type_enum',
    );
    $enums = array(
      'type_enum' => ProfilePictureSourceTypeValues::getInstance()->getValues(),
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/picture',
      new ProfilePictureSource(),
      'EDGE',
      ProfilePictureSource::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getProducts(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'product_ids' => 'list<string>',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/products',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getPurchases(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'is_premium' => 'bool',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/purchases',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function deleteRoles(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'user' => 'int',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_DELETE,
      '/roles',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getRoles(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/roles',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createRole(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'role' => 'role_enum',
      'user' => 'int',
    );
    $enums = array(
      'role_enum' => ApplicationRoleValues::getInstance()->getValues(),
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/roles',
      new Application(),
      'EDGE',
      Application::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createStagingResource(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'file' => 'file',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/stagingresources',
      new Application(),
      'EDGE',
      Application::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getSubscribedDomains(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/subscribed_domains',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createSubscribedDomain(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'subscribe' => 'list<string>',
      'unsubscribe' => 'list<string>',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/subscribed_domains',
      new Application(),
      'EDGE',
      Application::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getSubscribedDomainsPhishing(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/subscribed_domains_phishing',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createSubscribedDomainsPhishing(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'subscribe' => 'list<string>',
      'unsubscribe' => 'list<string>',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/subscribed_domains_phishing',
      new Application(),
      'EDGE',
      Application::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function deleteSubscriptions(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'fields' => 'list<string>',
      'object' => 'string',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_DELETE,
      '/subscriptions',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createSubscription(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'callback_url' => 'string',
      'fields' => 'list<string>',
      'include_values' => 'bool',
      'object' => 'string',
      'verify_token' => 'string',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/subscriptions',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createSubscriptionsSample(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'custom_fields' => 'string',
      'field' => 'string',
      'object' => 'string',
      'object_id' => 'string',
      'stress_run' => 'unsigned int',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/subscriptions_sample',
      new Application(),
      'EDGE',
      Application::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createUpload(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'file_length' => 'unsigned int',
      'file_name' => 'string',
      'file_type' => 'string',
      'session_type' => 'session_type_enum',
    );
    $enums = array(
      'session_type_enum' => array(
        'attachment',
      ),
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/uploads',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function createUserProperty(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'data' => 'list<Object>',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/user_properties',
      new AbstractCrudObject(),
      'EDGE',
      array(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function getSelf(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'advertiser_id' => 'string',
    );
    $enums = array(
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_GET,
      '/',
      new Application(),
      'NODE',
      Application::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

  public function updateSelf(array $fields = array(), array $params = array(), $pending = false) {
    $this->assureId();

    $param_types = array(
      'an_platforms' => 'list<an_platforms_enum>',
      'android_class_name' => 'string',
      'android_key_hash' => 'list<string>',
      'android_key_hashes' => 'list<string>',
      'android_package_name' => 'string',
      'android_sso' => 'bool',
      'app_domains' => 'list<string>',
      'app_name' => 'string',
      'app_type' => 'bool',
      'auth_dialog_data_help_url' => 'string',
      'auth_dialog_headline' => 'string',
      'auth_dialog_perms_explanation' => 'string',
      'auth_referral_default_activity_privacy' => 'string',
      'auth_referral_enabled' => 'bool',
      'auth_referral_extended_perms' => 'list<string>',
      'auth_referral_friend_perms' => 'list<string>',
      'auth_referral_response_type' => 'string',
      'auth_referral_user_perms' => 'list<string>',
      'canvas_fluid_height' => 'bool',
      'canvas_fluid_width' => 'bool',
      'canvas_url' => 'string',
      'category' => 'string',
      'configured_ios_sso' => 'bool',
      'contact_email' => 'string',
      'created_time' => 'Object',
      'creator_uid' => 'int',
      'deauth_callback_url' => 'string',
      'hosting_url' => 'string',
      'ios_bundle_id' => 'list<string>',
      'ipad_app_store_id' => 'string',
      'iphone_app_store_id' => 'string',
      'migrations' => 'string',
      'mobile_web_url' => 'string',
      'namespace' => 'string',
      'page_tab_default_name' => 'string',
      'page_tab_url' => 'string',
      'privacy_policy_url' => 'string',
      'restrictions' => 'string',
      'secure_canvas_url' => 'string',
      'secure_page_tab_url' => 'string',
      'server_ip_whitelist' => 'list<string>',
      'social_discovery' => 'bool',
      'subcategory' => 'string',
      'terms_of_service_url' => 'string',
      'url_scheme_suffix' => 'string',
      'user_support_email' => 'string',
      'user_support_url' => 'string',
      'website_url' => 'string',
    );
    $enums = array(
      'an_platforms_enum' => ApplicationAnPlatformsValues::getInstance()->getValues(),
    );

    $request = new ApiRequest(
      $this->api,
      $this->data['id'],
      RequestInterface::METHOD_POST,
      '/',
      new Application(),
      'NODE',
      Application::getFieldsEnum()->getValues(),
      new TypeChecker($param_types, $enums)
    );
    $request->addParams($params);
    $request->addFields($fields);
    return $pending ? $request : $request->execute();
  }

}
