<?php

namespace OAuth2\Storage;


/**
 * Keeps most information in memory.  Users are authenticated from the user table.  Currently all admins
 * are valid api users.
 */
class VCEStorage implements AuthorizationCodeInterface,
AccessTokenInterface,
ClientCredentialsInterface,
JwtBearerInterface,
PublicKeyInterface {
    public $authorizationCodes;
    public $clientCredentials;
    public $accessTokens;
    public $jwt;
    public $jti;
    public $keys;
    public $vce;

    public function __construct($params = array()) {
        $params = array_merge(array(
            'authorization_codes' => array(),
            'client_credentials' => array(),
            'access_tokens' => array(),
            'jwt' => array(),
            'jti' => array(),
            'keys' => array(),
        ), $params);

        $this->authorizationCodes = $params['authorization_codes'];
        $this->clientCredentials = $params['client_credentials'];
        $this->accessTokens = $params['access_tokens'];
        $this->jwt = $params['jwt'];
        $this->jti = $params['jti'];
        $this->keys = $params['keys'];
        $this->vce = $params['vce'];

        global $vce;

		// Create a relation between tags and content
		$vce->db->query("CREATE TABLE IF NOT EXISTS  {$vce->db->prefix}oauth_data (
      				id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    access_token LONGTEXT NOT NULL, 
                    client_id LONGTEXT NOT NULL, 
                    user_id LONGTEXT NOT NULL, 
                    expires INT NOT NULL,
                    scope LONGTEXT NOT NULL, 
                    id_token LONGTEXT NOT NULL,
					PRIMARY KEY  (id)
				  );");
    }

    /* AuthorizationCodeInterface */
    public function getAuthorizationCode($code) {
        if (!isset($this->authorizationCodes[$code])) {
            return false;
        }

        return array_merge(array(
            'authorization_code' => $code,
        ), $this->authorizationCodes[$code]);
    }

    public function setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null, $id_token = null) {
        $this->authorizationCodes[$code] = compact('code', 'client_id', 'user_id', 'redirect_uri', 'expires', 'scope', 'id_token');

        return true;
    }

    public function setAuthorizationCodes($authorization_codes) {
        $this->authorizationCodes = $authorization_codes;
    }

    public function expireAuthorizationCode($code) {
        unset($this->authorizationCodes[$code]);
    }

    /* ClientCredentialsInterface */
    public function checkClientCredentials($client_id, $client_secret = null) {

        global $vce;

        $hash = $vce->user->generate_hash($client_id, $client_secret);

        // get user_id
        $query = "SELECT user_id FROM " . TABLE_PREFIX . "users WHERE hash='" . $hash . "' LIMIT 1";
        $obj = $vce->db->get_data_object($query);

        return isset($obj[0]) && isset($obj[0]->user_id) && $obj[0]->user_id > 0;
    }

    public function isPublicClient($client_id) {
        return false;
    }

    /* ClientInterface */
    public function getClientDetails($client_id) {
        if (!isset($this->clientCredentials[$client_id])) {
            return false;
        }

        $clientDetails = array_merge(array(
            'client_id' => $client_id,
            'client_secret' => null,
            'redirect_uri' => null,
            'scope' => null,
        ), $this->clientCredentials[$client_id]);

        return $clientDetails;
    }

    public function checkRestrictedGrantType($client_id, $grant_type) {
        if (isset($this->clientCredentials[$client_id]['grant_types'])) {
            $grant_types = explode(' ', $this->clientCredentials[$client_id]['grant_types']);

            return in_array($grant_type, $grant_types);
        }

        // if grant_types are not defined, then none are restricted
        return true;
    }

    public function setClientDetails($client_id, $client_secret = null, $redirect_uri = null, $grant_types = null, $scope = null, $user_id = null) {
        $this->clientCredentials[$client_id] = array(
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => $redirect_uri,
            'grant_types' => $grant_types,
            'scope' => $scope,
            'user_id' => $user_id,
        );

        return true;
    }


    /* AccessTokenInterface */
    public function getAccessToken($access_token) {

        global $vce;
        $token = $vce->db->get_data_object(
			$vce->db->prepare("SELECT access_token FROM {$vce->db->prefix}oath_data WHERE access_token = %s", $access_token)
        );
        
        return $token != null;
    }

    public function setAccessToken($access_token, $client_id, $user_id, $expires, $scope = null, $id_token = null) {

        global $vce;

        // delete old records with same client_id
        $vce->db->delete('oauth_data', array('client_id' => $client_id));
        
		$vce->db->sanitized_insert(
			'oauth_data',
			array(
				'access_token' => $access_token,
				'client_id' => $client_id,
				'user_id' => $user_id,
				'expires' => $expires,
				'scope' => '',
				'id_token' => '',
			)
		);

        return true;
    }

    public function unsetAccessToken($access_token) {
        if (isset($this->accessTokens[$access_token])) {
            unset($this->accessTokens[$access_token]);
            global $vce;
            $vce->site->add_attributes('oauth2_tokens', $this->accessTokens, true);

            return true;
        }

        return false;
    }

    public function scopeExists($scope) {
        $scope = explode(' ', trim($scope));

        return (count(array_diff($scope, $this->supportedScopes)) == 0);
    }

    public function getDefaultScope($client_id = null) {
        return $this->defaultScope;
    }

    /*JWTBearerInterface */
    public function getClientKey($client_id, $subject) {
        if (isset($this->jwt[$client_id])) {
            $jwt = $this->jwt[$client_id];
            if ($jwt) {
                if ($jwt["subject"] == $subject) {
                    return $jwt["key"];
                }
            }
        }

        return false;
    }

    public function getClientScope($client_id) {
        if (!$clientDetails = $this->getClientDetails($client_id)) {
            return false;
        }

        if (isset($clientDetails['scope'])) {
            return $clientDetails['scope'];
        }

        return null;
    }

    public function getJti($client_id, $subject, $audience, $expires, $jti) {
        foreach ($this->jti as $storedJti) {
            if ($storedJti['issuer'] == $client_id && $storedJti['subject'] == $subject && $storedJti['audience'] == $audience && $storedJti['expires'] == $expires && $storedJti['jti'] == $jti) {
                return array(
                    'issuer' => $storedJti['issuer'],
                    'subject' => $storedJti['subject'],
                    'audience' => $storedJti['audience'],
                    'expires' => $storedJti['expires'],
                    'jti' => $storedJti['jti'],
                );
            }
        }

        return null;
    }

    public function setJti($client_id, $subject, $audience, $expires, $jti) {
        $this->jti[] = array('issuer' => $client_id, 'subject' => $subject, 'audience' => $audience, 'expires' => $expires, 'jti' => $jti);
    }

    /*PublicKeyInterface */
    public function getPublicKey($client_id = null) {
        if (isset($this->keys[$client_id])) {
            return $this->keys[$client_id]['public_key'];
        }

        // use a global encryption pair
        if (isset($this->keys['public_key'])) {
            return $this->keys['public_key'];
        }

        return false;
    }

    public function getPrivateKey($client_id = null) {
        if (isset($this->keys[$client_id])) {
            return $this->keys[$client_id]['private_key'];
        }

        // use a global encryption pair
        if (isset($this->keys['private_key'])) {
            return $this->keys['private_key'];
        }

        return false;
    }

    public function getEncryptionAlgorithm($client_id = null) {
        if (isset($this->keys[$client_id]['encryption_algorithm'])) {
            return $this->keys[$client_id]['encryption_algorithm'];
        }

        // use a global encryption algorithm
        if (isset($this->keys['encryption_algorithm'])) {
            return $this->keys['encryption_algorithm'];
        }

        return 'RS256';
    }
}