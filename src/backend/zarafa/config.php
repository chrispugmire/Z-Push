<?php
/***********************************************
* File      :   config.php
* Project   :   Z-Push
* Descr     :   Zarafa backend configuration file
*
* Created   :   27.11.2012
*
* Copyright 2007 - 2016 Zarafa Deutschland GmbH
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License, version 3,
* as published by the Free Software Foundation with the following additional
* term according to sec. 7:
*
* According to sec. 7 of the GNU Affero General Public License, version 3,
* the terms of the AGPL are supplemented with the following terms:
*
* "Zarafa" is a registered trademark of Zarafa B.V.
* "Z-Push" is a registered trademark of Zarafa Deutschland GmbH
* The licensing of the Program under the AGPL does not imply a trademark license.
* Therefore any rights, title and interest in our trademarks remain entirely with us.
*
* However, if you propagate an unmodified version of the Program you are
* allowed to use the term "Z-Push" to indicate that you distribute the Program.
* Furthermore you may use our trademarks where it is necessary to indicate
* the intended purpose of a product or service provided you use it in accordance
* with honest practices in industrial or commercial matters.
* If you want to propagate modified versions of the Program under the name "Z-Push",
* you may only do so if you have a written permission by Zarafa Deutschland GmbH
* (to acquire a permission please contact Zarafa at trademark@zarafa.com).
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU Affero General Public License for more details.
*
* You should have received a copy of the GNU Affero General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* Consult LICENSE file for details
************************************************/

// ************************
//  BackendZarafa settings
// ************************

// Defines the server to which we want to connect
define('MAPI_SERVER', 'http://127.0.0.1:236/zarafa');

// Read-Only shared folders
//   When trying to write a change on a read-only folder this data is dropped and replaced on the device of the user.
//   Enabling the option below, sends an email to the user notifying that this happened (default enabled).
//   If this is disabled, the data will be dropped silently and will be lost.
//   The template of the email sent can be customized here. The placeholders can also be used in the subject.
define('READ_ONLY_NOTIFY_LOST_DATA', true);
// String to mark the data changed by the user (that he is trying to save)
define('READ_ONLY_NOTIFY_YOURDATA', 'Your data');
// Email template to be sent to the user
define('READ_ONLY_NOTIFY_SUBJECT', "Z-Push: Writing operation not permitted - data reset");
define('READ_ONLY_NOTIFY_BODY', <<<END
Dear **USERFULLNAME**,

on **DATE** at **TIME** you've tried to save a data in the folder '**FOLDERNAME**' on your device '**MOBILETYPE**' ID: '**MOBILEDEVICEID**'.

This operation was not successful, as you lack write access to this folder.
Your data has been dropped and replaced with the original data on your device to ensure data integrity.

Below the data you tried to save. You should seek other means to it (e.g. forward this email to a person with write access to this folder).

**DIFFERENCES**

If you have questions about this email, please contact your e-mail administrator.

Sincerely,
Your Z-Push system
END
         );
// Format of the **DATE** and **TIME** placeholders - more information on formats, see http://php.net/manual/en/function.strftime.php
define('READ_ONLY_NOTIFY_DATE_FORMAT', "%d.%m.%Y");
define('READ_ONLY_NOTIFY_TIME_FORMAT', "%H:%M:%S");
