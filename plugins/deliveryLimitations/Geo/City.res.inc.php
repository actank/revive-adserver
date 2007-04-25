<?php

/*
+---------------------------------------------------------------------------+
| Openads v2.3                                                              |
| =============                                                             |
|                                                                           |
| Copyright (c) 2003-2007 Openads Ltd                                       |
| For contact details, see: http://www.openads.org/                         |
|                                                                           |
| This program is free software; you can redistribute it and/or modify      |
| it under the terms of the GNU General Public License as published by      |
| the Free Software Foundation; either version 2 of the License, or         |
| (at your option) any later version.                                       |
|                                                                           |
| This program is distributed in the hope that it will be useful,           |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU General Public License for more details.                              |
|                                                                           |
| You should have received a copy of the GNU General Public License         |
| along with this program; if not, write to the Free Software               |
| Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
+---------------------------------------------------------------------------+
$Id$
*/

/**
 * @package    MaxPlugin
 * @subpackage DeliveryLimitations
 * @author     Chris Nutting <chris@m3.net>
 */

$res = array(
	'AD' => MAX_Plugin_Translation::translate('Andorra', $this->module, $this->package),
	'AE' => MAX_Plugin_Translation::translate('United Arab Emirates', $this->module, $this->package),
	'AF' => MAX_Plugin_Translation::translate('Afghanistan', $this->module, $this->package),
	'AG' => MAX_Plugin_Translation::translate('Antigua and Barbuda', $this->module, $this->package),
	'AI' => MAX_Plugin_Translation::translate('Anguilla', $this->module, $this->package),
	'AL' => MAX_Plugin_Translation::translate('Albania', $this->module, $this->package),
	'AM' => MAX_Plugin_Translation::translate('Armenia', $this->module, $this->package),
	'AN' => MAX_Plugin_Translation::translate('Netherlands Antilles', $this->module, $this->package),
	'AO' => MAX_Plugin_Translation::translate('Angola', $this->module, $this->package),
	'AP' => MAX_Plugin_Translation::translate('Asia/Pacific Region', $this->module, $this->package),
	'AQ' => MAX_Plugin_Translation::translate('Antarctica', $this->module, $this->package),
	'AR' => MAX_Plugin_Translation::translate('Argentina', $this->module, $this->package),
	'AS' => MAX_Plugin_Translation::translate('American Samoa', $this->module, $this->package),
	'AT' => MAX_Plugin_Translation::translate('Austria', $this->module, $this->package),
	'AU' => MAX_Plugin_Translation::translate('Australia', $this->module, $this->package),
	'AW' => MAX_Plugin_Translation::translate('Aruba', $this->module, $this->package),
	'AZ' => MAX_Plugin_Translation::translate('Azerbaijan', $this->module, $this->package),
	'BA' => MAX_Plugin_Translation::translate('Bosnia and Herzegovina', $this->module, $this->package),
	'BB' => MAX_Plugin_Translation::translate('Barbados', $this->module, $this->package),
	'BD' => MAX_Plugin_Translation::translate('Bangladesh', $this->module, $this->package),
	'BE' => MAX_Plugin_Translation::translate('Belgium', $this->module, $this->package),
	'BF' => MAX_Plugin_Translation::translate('Burkina Faso', $this->module, $this->package),
	'BG' => MAX_Plugin_Translation::translate('Bulgaria', $this->module, $this->package),
	'BH' => MAX_Plugin_Translation::translate('Bahrain', $this->module, $this->package),
	'BI' => MAX_Plugin_Translation::translate('Burundi', $this->module, $this->package),
	'BJ' => MAX_Plugin_Translation::translate('Benin', $this->module, $this->package),
	'BM' => MAX_Plugin_Translation::translate('Bermuda', $this->module, $this->package),
	'BN' => MAX_Plugin_Translation::translate('Brunei Darussalam', $this->module, $this->package),
	'BO' => MAX_Plugin_Translation::translate('Bolivia', $this->module, $this->package),
	'BR' => MAX_Plugin_Translation::translate('Brazil', $this->module, $this->package),
	'BS' => MAX_Plugin_Translation::translate('Bahamas', $this->module, $this->package),
	'BT' => MAX_Plugin_Translation::translate('Bhutan', $this->module, $this->package),
	'BV' => MAX_Plugin_Translation::translate('Bouvet Island', $this->module, $this->package),
	'BW' => MAX_Plugin_Translation::translate('Botswana', $this->module, $this->package),
	'BY' => MAX_Plugin_Translation::translate('Belarus', $this->module, $this->package),
	'BZ' => MAX_Plugin_Translation::translate('Belize', $this->module, $this->package),
	'CA' => MAX_Plugin_Translation::translate('Canada', $this->module, $this->package),
	'CC' => MAX_Plugin_Translation::translate('Cocos (Keeling) Islands', $this->module, $this->package),
	'CD' => MAX_Plugin_Translation::translate('Congo - The Democratic Republic of the', $this->module, $this->package),
	'CF' => MAX_Plugin_Translation::translate('Central African Republic', $this->module, $this->package),
	'CG' => MAX_Plugin_Translation::translate('Congo', $this->module, $this->package),
	'CH' => MAX_Plugin_Translation::translate('Switzerland', $this->module, $this->package),
	'CI' => MAX_Plugin_Translation::translate('Cote D\'Ivoire', $this->module, $this->package),
	'CK' => MAX_Plugin_Translation::translate('Cook Islands', $this->module, $this->package),
	'CL' => MAX_Plugin_Translation::translate('Chile', $this->module, $this->package),
	'CM' => MAX_Plugin_Translation::translate('Cameroon', $this->module, $this->package),
	'CN' => MAX_Plugin_Translation::translate('China', $this->module, $this->package),
	'CO' => MAX_Plugin_Translation::translate('Colombia', $this->module, $this->package),
	'CR' => MAX_Plugin_Translation::translate('Costa Rica', $this->module, $this->package),
	'CU' => MAX_Plugin_Translation::translate('Cuba', $this->module, $this->package),
	'CV' => MAX_Plugin_Translation::translate('Cape Verde', $this->module, $this->package),
	'CX' => MAX_Plugin_Translation::translate('Christmas Island', $this->module, $this->package),
	'CY' => MAX_Plugin_Translation::translate('Cyprus', $this->module, $this->package),
	'CZ' => MAX_Plugin_Translation::translate('Czech Republic', $this->module, $this->package),
	'DE' => MAX_Plugin_Translation::translate('Germany', $this->module, $this->package),
	'DJ' => MAX_Plugin_Translation::translate('Djibouti', $this->module, $this->package),
	'DK' => MAX_Plugin_Translation::translate('Denmark', $this->module, $this->package),
	'DM' => MAX_Plugin_Translation::translate('Dominica', $this->module, $this->package),
	'DO' => MAX_Plugin_Translation::translate('Dominican Republic', $this->module, $this->package),
	'DZ' => MAX_Plugin_Translation::translate('Algeria', $this->module, $this->package),
	'EC' => MAX_Plugin_Translation::translate('Ecuador', $this->module, $this->package),
	'EE' => MAX_Plugin_Translation::translate('Estonia', $this->module, $this->package),
	'EG' => MAX_Plugin_Translation::translate('Egypt', $this->module, $this->package),
	'EH' => MAX_Plugin_Translation::translate('Western Sahara', $this->module, $this->package),
	'ER' => MAX_Plugin_Translation::translate('Eritrea', $this->module, $this->package),
	'ES' => MAX_Plugin_Translation::translate('Spain', $this->module, $this->package),
	'ET' => MAX_Plugin_Translation::translate('Ethiopia', $this->module, $this->package),
	'EU' => MAX_Plugin_Translation::translate('Europe', $this->module, $this->package),
	'FI' => MAX_Plugin_Translation::translate('Finland', $this->module, $this->package),
	'FJ' => MAX_Plugin_Translation::translate('Fiji', $this->module, $this->package),
	'FK' => MAX_Plugin_Translation::translate('Falkland Islands (Malvinas)', $this->module, $this->package),
	'FM' => MAX_Plugin_Translation::translate('Micronesia - Federated States of', $this->module, $this->package),
	'FO' => MAX_Plugin_Translation::translate('Faroe Islands', $this->module, $this->package),
	'FR' => MAX_Plugin_Translation::translate('France', $this->module, $this->package),
	'FX' => MAX_Plugin_Translation::translate('France - Metropolitan', $this->module, $this->package),
	'GA' => MAX_Plugin_Translation::translate('Gabon', $this->module, $this->package),
	'GB' => MAX_Plugin_Translation::translate('United Kingdom', $this->module, $this->package),
	'GD' => MAX_Plugin_Translation::translate('Grenada', $this->module, $this->package),
	'GE' => MAX_Plugin_Translation::translate('Georgia', $this->module, $this->package),
	'GF' => MAX_Plugin_Translation::translate('French Guiana', $this->module, $this->package),
	'GH' => MAX_Plugin_Translation::translate('Ghana', $this->module, $this->package),
	'GI' => MAX_Plugin_Translation::translate('Gibraltar', $this->module, $this->package),
	'GL' => MAX_Plugin_Translation::translate('Greenland', $this->module, $this->package),
	'GM' => MAX_Plugin_Translation::translate('Gambia', $this->module, $this->package),
	'GN' => MAX_Plugin_Translation::translate('Guinea', $this->module, $this->package),
	'GP' => MAX_Plugin_Translation::translate('Guadeloupe', $this->module, $this->package),
	'GQ' => MAX_Plugin_Translation::translate('Equatorial Guinea', $this->module, $this->package),
	'GR' => MAX_Plugin_Translation::translate('Greece', $this->module, $this->package),
	'GS' => MAX_Plugin_Translation::translate('South Georgia and the South Sandwich Islands', $this->module, $this->package),
	'GT' => MAX_Plugin_Translation::translate('Guatemala', $this->module, $this->package),
	'GU' => MAX_Plugin_Translation::translate('Guam', $this->module, $this->package),
	'GW' => MAX_Plugin_Translation::translate('Guinea-Bissau', $this->module, $this->package),
	'GY' => MAX_Plugin_Translation::translate('Guyana', $this->module, $this->package),
	'HK' => MAX_Plugin_Translation::translate('Hong Kong', $this->module, $this->package),
	'HM' => MAX_Plugin_Translation::translate('Heard Island and McDonald Islands', $this->module, $this->package),
	'HN' => MAX_Plugin_Translation::translate('Honduras', $this->module, $this->package),
	'HR' => MAX_Plugin_Translation::translate('Croatia', $this->module, $this->package),
	'HT' => MAX_Plugin_Translation::translate('Haiti', $this->module, $this->package),
	'HU' => MAX_Plugin_Translation::translate('Hungary', $this->module, $this->package),
	'ID' => MAX_Plugin_Translation::translate('Indonesia', $this->module, $this->package),
	'IE' => MAX_Plugin_Translation::translate('Ireland', $this->module, $this->package),
	'IL' => MAX_Plugin_Translation::translate('Israel', $this->module, $this->package),
	'IN' => MAX_Plugin_Translation::translate('India', $this->module, $this->package),
	'IO' => MAX_Plugin_Translation::translate('British Indian Ocean Territory', $this->module, $this->package),
	'IQ' => MAX_Plugin_Translation::translate('Iraq', $this->module, $this->package),
	'IR' => MAX_Plugin_Translation::translate('Iran - Islamic Republic of', $this->module, $this->package),
	'IS' => MAX_Plugin_Translation::translate('Iceland', $this->module, $this->package),
	'IT' => MAX_Plugin_Translation::translate('Italy', $this->module, $this->package),
	'JM' => MAX_Plugin_Translation::translate('Jamaica', $this->module, $this->package),
	'JO' => MAX_Plugin_Translation::translate('Jordan', $this->module, $this->package),
	'JP' => MAX_Plugin_Translation::translate('Japan', $this->module, $this->package),
	'KE' => MAX_Plugin_Translation::translate('Kenya', $this->module, $this->package),
	'KG' => MAX_Plugin_Translation::translate('Kyrgyzstan', $this->module, $this->package),
	'KH' => MAX_Plugin_Translation::translate('Cambodia', $this->module, $this->package),
	'KI' => MAX_Plugin_Translation::translate('Kiribati', $this->module, $this->package),
	'KM' => MAX_Plugin_Translation::translate('Comoros', $this->module, $this->package),
	'KN' => MAX_Plugin_Translation::translate('Saint Kitts and Nevis', $this->module, $this->package),
	'KP' => MAX_Plugin_Translation::translate('Korea - Democratic People\'s Republic of', $this->module, $this->package),
	'KR' => MAX_Plugin_Translation::translate('Korea - Republic of', $this->module, $this->package),
	'KW' => MAX_Plugin_Translation::translate('Kuwait', $this->module, $this->package),
	'KY' => MAX_Plugin_Translation::translate('Cayman Islands', $this->module, $this->package),
	'KZ' => MAX_Plugin_Translation::translate('Kazakhstan', $this->module, $this->package),
	'LA' => MAX_Plugin_Translation::translate('Lao People\'s Democratic Republic', $this->module, $this->package),
	'LB' => MAX_Plugin_Translation::translate('Lebanon', $this->module, $this->package),
	'LC' => MAX_Plugin_Translation::translate('Saint Lucia', $this->module, $this->package),
	'LI' => MAX_Plugin_Translation::translate('Liechtenstein', $this->module, $this->package),
	'LK' => MAX_Plugin_Translation::translate('Sri Lanka', $this->module, $this->package),
	'LR' => MAX_Plugin_Translation::translate('Liberia', $this->module, $this->package),
	'LS' => MAX_Plugin_Translation::translate('Lesotho', $this->module, $this->package),
	'LT' => MAX_Plugin_Translation::translate('Lithuania', $this->module, $this->package),
	'LU' => MAX_Plugin_Translation::translate('Luxembourg', $this->module, $this->package),
	'LV' => MAX_Plugin_Translation::translate('Latvia', $this->module, $this->package),
	'LY' => MAX_Plugin_Translation::translate('Libyan Arab Jamahiriya', $this->module, $this->package),
	'MA' => MAX_Plugin_Translation::translate('Morocco', $this->module, $this->package),
	'MC' => MAX_Plugin_Translation::translate('Monaco', $this->module, $this->package),
	'MD' => MAX_Plugin_Translation::translate('Moldova - Republic of', $this->module, $this->package),
	'MG' => MAX_Plugin_Translation::translate('Madagascar', $this->module, $this->package),
	'MH' => MAX_Plugin_Translation::translate('Marshall Islands', $this->module, $this->package),
	'MK' => MAX_Plugin_Translation::translate('Macedonia', $this->module, $this->package),
	'ML' => MAX_Plugin_Translation::translate('Mali', $this->module, $this->package),
	'MM' => MAX_Plugin_Translation::translate('Myanmar', $this->module, $this->package),
	'MN' => MAX_Plugin_Translation::translate('Mongolia', $this->module, $this->package),
	'MO' => MAX_Plugin_Translation::translate('Macau', $this->module, $this->package),
	'MP' => MAX_Plugin_Translation::translate('Northern Mariana Islands', $this->module, $this->package),
	'MQ' => MAX_Plugin_Translation::translate('Martinique', $this->module, $this->package),
	'MR' => MAX_Plugin_Translation::translate('Mauritania', $this->module, $this->package),
	'MS' => MAX_Plugin_Translation::translate('Montserrat', $this->module, $this->package),
	'MT' => MAX_Plugin_Translation::translate('Malta', $this->module, $this->package),
	'MU' => MAX_Plugin_Translation::translate('Mauritius', $this->module, $this->package),
	'MV' => MAX_Plugin_Translation::translate('Maldives', $this->module, $this->package),
	'MW' => MAX_Plugin_Translation::translate('Malawi', $this->module, $this->package),
	'MX' => MAX_Plugin_Translation::translate('Mexico', $this->module, $this->package),
	'MY' => MAX_Plugin_Translation::translate('Malaysia', $this->module, $this->package),
	'MZ' => MAX_Plugin_Translation::translate('Mozambique', $this->module, $this->package),
	'NA' => MAX_Plugin_Translation::translate('Namibia', $this->module, $this->package),
	'NC' => MAX_Plugin_Translation::translate('New Caledonia', $this->module, $this->package),
	'NE' => MAX_Plugin_Translation::translate('Niger', $this->module, $this->package),
	'NF' => MAX_Plugin_Translation::translate('Norfolk Island', $this->module, $this->package),
	'NG' => MAX_Plugin_Translation::translate('Nigeria', $this->module, $this->package),
	'NI' => MAX_Plugin_Translation::translate('Nicaragua', $this->module, $this->package),
	'NL' => MAX_Plugin_Translation::translate('Netherlands', $this->module, $this->package),
	'NO' => MAX_Plugin_Translation::translate('Norway', $this->module, $this->package),
	'NP' => MAX_Plugin_Translation::translate('Nepal', $this->module, $this->package),
	'NR' => MAX_Plugin_Translation::translate('Nauru', $this->module, $this->package),
	'NU' => MAX_Plugin_Translation::translate('Niue', $this->module, $this->package),
	'NZ' => MAX_Plugin_Translation::translate('New Zealand', $this->module, $this->package),
	'OM' => MAX_Plugin_Translation::translate('Oman', $this->module, $this->package),
	'PA' => MAX_Plugin_Translation::translate('Panama', $this->module, $this->package),
	'PE' => MAX_Plugin_Translation::translate('Peru', $this->module, $this->package),
	'PF' => MAX_Plugin_Translation::translate('French Polynesia', $this->module, $this->package),
	'PG' => MAX_Plugin_Translation::translate('Papua New Guinea', $this->module, $this->package),
	'PH' => MAX_Plugin_Translation::translate('Philippines', $this->module, $this->package),
	'PK' => MAX_Plugin_Translation::translate('Pakistan', $this->module, $this->package),
	'PL' => MAX_Plugin_Translation::translate('Poland', $this->module, $this->package),
	'PM' => MAX_Plugin_Translation::translate('Saint Pierre and Miquelon', $this->module, $this->package),
	'PN' => MAX_Plugin_Translation::translate('Pitcairn', $this->module, $this->package),
	'PR' => MAX_Plugin_Translation::translate('Puerto Rico', $this->module, $this->package),
	'PS' => MAX_Plugin_Translation::translate('Palestinian Territory - Occupied', $this->module, $this->package),
	'PT' => MAX_Plugin_Translation::translate('Portugal', $this->module, $this->package),
	'PW' => MAX_Plugin_Translation::translate('Palau', $this->module, $this->package),
	'PY' => MAX_Plugin_Translation::translate('Paraguay', $this->module, $this->package),
	'QA' => MAX_Plugin_Translation::translate('Qatar', $this->module, $this->package),
	'RE' => MAX_Plugin_Translation::translate('Reunion', $this->module, $this->package),
	'RO' => MAX_Plugin_Translation::translate('Romania', $this->module, $this->package),
	'RU' => MAX_Plugin_Translation::translate('Russian Federation', $this->module, $this->package),
	'RW' => MAX_Plugin_Translation::translate('Rwanda', $this->module, $this->package),
	'SA' => MAX_Plugin_Translation::translate('Saudi Arabia', $this->module, $this->package),
	'SB' => MAX_Plugin_Translation::translate('Solomon Islands', $this->module, $this->package),
	'SC' => MAX_Plugin_Translation::translate('Seychelles', $this->module, $this->package),
	'SD' => MAX_Plugin_Translation::translate('Sudan', $this->module, $this->package),
	'SE' => MAX_Plugin_Translation::translate('Sweden', $this->module, $this->package),
	'SG' => MAX_Plugin_Translation::translate('Singapore', $this->module, $this->package),
	'SH' => MAX_Plugin_Translation::translate('Saint Helena', $this->module, $this->package),
	'SI' => MAX_Plugin_Translation::translate('Slovenia', $this->module, $this->package),
	'SJ' => MAX_Plugin_Translation::translate('Svalbard and Jan Mayen', $this->module, $this->package),
	'SK' => MAX_Plugin_Translation::translate('Slovakia', $this->module, $this->package),
	'SL' => MAX_Plugin_Translation::translate('Sierra Leone', $this->module, $this->package),
	'SM' => MAX_Plugin_Translation::translate('San Marino', $this->module, $this->package),
	'SN' => MAX_Plugin_Translation::translate('Senegal', $this->module, $this->package),
	'SO' => MAX_Plugin_Translation::translate('Somalia', $this->module, $this->package),
	'SR' => MAX_Plugin_Translation::translate('Suriname', $this->module, $this->package),
	'ST' => MAX_Plugin_Translation::translate('Sao Tome and Principe', $this->module, $this->package),
	'SV' => MAX_Plugin_Translation::translate('El Salvador', $this->module, $this->package),
	'SY' => MAX_Plugin_Translation::translate('Syrian Arab Republic', $this->module, $this->package),
	'SZ' => MAX_Plugin_Translation::translate('Swaziland', $this->module, $this->package),
	'TC' => MAX_Plugin_Translation::translate('Turks and Caicos Islands', $this->module, $this->package),
	'TD' => MAX_Plugin_Translation::translate('Chad', $this->module, $this->package),
	'TF' => MAX_Plugin_Translation::translate('French Southern Territories', $this->module, $this->package),
	'TG' => MAX_Plugin_Translation::translate('Togo', $this->module, $this->package),
	'TH' => MAX_Plugin_Translation::translate('Thailand', $this->module, $this->package),
	'TJ' => MAX_Plugin_Translation::translate('Tajikistan', $this->module, $this->package),
	'TK' => MAX_Plugin_Translation::translate('Tokelau', $this->module, $this->package),
	'TL' => MAX_Plugin_Translation::translate('East Timor', $this->module, $this->package),
	'TM' => MAX_Plugin_Translation::translate('Turkmenistan', $this->module, $this->package),
	'TN' => MAX_Plugin_Translation::translate('Tunisia', $this->module, $this->package),
	'TO' => MAX_Plugin_Translation::translate('Tonga', $this->module, $this->package),
	'TR' => MAX_Plugin_Translation::translate('Turkey', $this->module, $this->package),
	'TT' => MAX_Plugin_Translation::translate('Trinidad and Tobago', $this->module, $this->package),
	'TV' => MAX_Plugin_Translation::translate('Tuvalu', $this->module, $this->package),
	'TW' => MAX_Plugin_Translation::translate('Taiwan - Province of China', $this->module, $this->package),
	'TZ' => MAX_Plugin_Translation::translate('Tanzania - United Republic of', $this->module, $this->package),
	'UA' => MAX_Plugin_Translation::translate('Ukraine', $this->module, $this->package),
	'UG' => MAX_Plugin_Translation::translate('Uganda', $this->module, $this->package),
	'UM' => MAX_Plugin_Translation::translate('United States Minor Outlying Islands', $this->module, $this->package),
	'US' => MAX_Plugin_Translation::translate('United States', $this->module, $this->package),
	'UY' => MAX_Plugin_Translation::translate('Uruguay', $this->module, $this->package),
	'UZ' => MAX_Plugin_Translation::translate('Uzbekistan', $this->module, $this->package),
	'VA' => MAX_Plugin_Translation::translate('Holy See (Vatican City State)', $this->module, $this->package),
	'VC' => MAX_Plugin_Translation::translate('Saint Vincent and the Grenadines', $this->module, $this->package),
	'VE' => MAX_Plugin_Translation::translate('Venezuela', $this->module, $this->package),
	'VG' => MAX_Plugin_Translation::translate('Virgin Islands - British', $this->module, $this->package),
	'VI' => MAX_Plugin_Translation::translate('Virgin Islands - U.S.', $this->module, $this->package),
	'VN' => MAX_Plugin_Translation::translate('Vietnam', $this->module, $this->package),
	'VU' => MAX_Plugin_Translation::translate('Vanuatu', $this->module, $this->package),
	'WF' => MAX_Plugin_Translation::translate('Wallis and Futuna', $this->module, $this->package),
	'WS' => MAX_Plugin_Translation::translate('Samoa', $this->module, $this->package),
	'YE' => MAX_Plugin_Translation::translate('Yemen', $this->module, $this->package),
	'YT' => MAX_Plugin_Translation::translate('Mayotte', $this->module, $this->package),
	'YU' => MAX_Plugin_Translation::translate('Yugoslavia', $this->module, $this->package),
	'ZA' => MAX_Plugin_Translation::translate('South Africa', $this->module, $this->package),
	'ZM' => MAX_Plugin_Translation::translate('Zambia', $this->module, $this->package),
	'ZR' => MAX_Plugin_Translation::translate('Zaire', $this->module, $this->package),
	'ZW' => MAX_Plugin_Translation::translate('Zimbabwe', $this->module, $this->package),
);

asort($res);
?>