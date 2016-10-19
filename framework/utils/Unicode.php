<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)


/// @description Unicode string manipulation class.
class Utils_Unicode {
    static function validate($str, $truncated = false) {
        $length = strlen($str);
        if ($length == 0) {
            return true;
        }
        for ($i = 0; $i < $length; $i++) {
            $high = ord($str{$i});
            if ($high < 0x80) {
                continue;
            } else {
                if ($high <= 0xC1) {
                    return false;
                } else {
                    if ($high < 0xE0) {
                        if (++$i >= $length) {
                            return $truncated;
                        } else {
                            if (($str{$i} & "\xC0") == "\x80") {
                                continue;
                            }
                        }
                    } else {
                        if ($high < 0xF0) {
                            if (++$i >= $length) {
                                return $truncated;
                            } else {
                                if (($str{$i} & "\xC0") == "\x80") {
                                    if (++$i >= $length) {
                                        return $truncated;
                                    } else {
                                        if (($str{$i} & "\xC0") == "\x80") {
                                            continue;
                                        }
                                    }
                                }
                            }
                        } else {
                            if ($high < 0xF5) {
                                if (++$i >= $length) {
                                    return $truncated;
                                } else {
                                    if (($str{$i} & "\xC0") == "\x80") {
                                        if (++$i >= $length) {
                                            return $truncated;
                                        } else {
                                            if (($str{$i} & "\xC0") == "\x80") {
                                                if (++$i >= $length) {
                                                    return $truncated;
                                                } else {
                                                    if (($str{$i} & "\xC0") == "\x80") {
                                                        continue;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } // F5~FF is invalid by RFC 3629
            return false;
        }
        return true;
    }

    static function correct($str, $broken = '') {
        $corrected = '';
        $strlen = strlen($str);
        for ($i = 0; $i < $strlen; $i++) {
            switch ($str{$i}) {
                case "\x09":
                case "\x0A":
                case "\x0D":
                    $corrected .= $str{$i};
                    break;
                case "\x7F":
                    $corrected .= $broken;
                    break;
                default:
                    $high = ord($str{$i});
                    if ($high < 0x20) { // Special Characters.
                        $corrected .= $broken;
                    } else {
                        if ($high < 0x80) { // 1byte.
                            $corrected .= $str{$i};
                        } else {
                            if ($high <= 0xC1) {
                                $corrected .= $broken;
                            } else {
                                if ($high < 0xE0) { // 2byte.
                                    if (($i + 1 >= $strlen) || (($str{$i + 1} & "\xC0") != "\x80")) {
                                        $corrected .= $broken;
                                    } else {
                                        $corrected .= $str{$i} . $str{$i + 1};
                                    }
                                    $i += 1;
                                } else {
                                    if ($high < 0xF0) { // 3byte.
                                        if (($i + 2 >= $strlen) || (($str{$i + 1} & "\xC0") != "\x80") || (($str{$i + 2} & "\xC0") != "\x80")) {
                                            $corrected .= $broken;
                                        } else {
                                            $corrected .= $str{$i} . $str{$i + 1} . $str{$i + 2};
                                        }
                                        $i += 2;
                                    } else {
                                        if ($high < 0xF5) { // 4byte.
                                            if (($i + 3 >= $strlen) || (($str{$i + 1} & "\xC0") != "\x80") || (($str{$i + 2} & "\xC0") != "\x80") || (($str{$i + 3} & "\xC0") != "\x80")) {
                                                $corrected .= $broken;
                                            } else {
                                                $corrected .= $str{$i} . $str{$i + 1} . $str{$i + 2} . $str{$i + 3};
                                            }
                                            $i += 3;
                                        } else { // F5~FF is invalid by RFC3629.
                                            $corrected .= $broken;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    break;
            }
        }
        if (preg_match('/&#([0-9]{1,});/', $corrected)) {
            $corrected = mb_decode_numericentity($corrected, array(0x0, 0x10000, 0, 0xfffff), 'UTF-8');
        }
        return $corrected;
    }

    static function bring($str, $encoding = null) {
        $context = Model_Context::getInstance();
        return @iconv((isset($encoding) ? $encoding : $context->getProperty('service.encoding')), 'UTF-8', $str);
    }

    static function convert($str, $encoding = null) {
        $context = Model_Context::getInstance();
        return @iconv('UTF-8', (isset($encoding) ? $encoding : $context->getProperty('service.encoding')), $str);
    }

    static function length($str) {
        if (function_exists('mb_strlen')) {
            return mb_strlen($str, 'utf-8');
        }
        $len = strlen($str);
        for ($i = $length = 0; $i < $len; $length++) {
            $high = ord($str{$i});
            if ($high < 0x80) {
                $i += 1;
            } else {
                if ($high < 0xE0) {
                    $i += 2;
                } else {
                    if ($high < 0xF0) {
                        $i += 3;
                    } else {
                        $i += 4;
                    }
                }
            }
        }
        return $length;
    }

    static function lengthAsEm($str) {
        if (function_exists('mb_strwidth')) {
            return mb_strwidth($str, 'utf-8');
        }
        $len = strlen($str);
        for ($i = $length = 0; $i < $len;) {
            $high = ord($str{$i});
            if ($high < 0x80) {
                $i += 1;
                $length += 1;
            } else {
                if ($high < 0xE0) {
                    $i += 2;
                } else {
                    if ($high < 0xF0) {
                        $i += 3;
                    } else {
                        $i += 4;
                    }
                }
                $length += 2;
            }
        }
        return $length;
    }

    static function lessenAsEncoding($str, $length = 255, $tail = '...') {
        $context = Model_Context::getInstance();
        if ($context->getProperty('database.utf8') != true) {
            return Utils_Unicode::lessen($str, $length, $tail);
        } else {
            return Utils_Unicode::lessenAsByte($str, $length, $tail);
        }
    }

    static function lessen($str, $chars, $tail = '...') {
        if (Utils_Unicode::length($str) <= $chars) {
            $tail = '';
        } else {
            $chars -= Utils_Unicode::length($tail);
        }
        if (function_exists('mb_substr')) {
            return mb_substr($str, 0, $chars, 'utf-8') . $tail;
        }

        $len = strlen($str);
        for ($i = $adapted = 0; $i < $len; $adapted = $i) {
            $high = ord($str{$i});
            if ($high < 0x80) {
                $i += 1;
            } else {
                if ($high < 0xE0) {
                    $i += 2;
                } else {
                    if ($high < 0xF0) {
                        $i += 3;
                    } else {
                        $i += 4;
                    }
                }
            }
            if (--$chars < 0) {
                break;
            }
        }
        return trim(substr($str, 0, $adapted)) . $tail;
    }

    static function lessenAsByte($str, $bytes, $tail = '...') {
        if (strlen($str) <= $bytes) {
            return $str;
        } else {
            $bytes -= strlen($tail);
        }

        $siclVerifyInt = ord($str[$bytes - 1]) >> 6;
        if (($siclVerifyInt >> 1) === 0) // 1byte
        {
            $siclLocationSubtInt = 0;
        } elseif ($siclVerifyInt === 3) // Head byte of multi-bytes characters.
        {
            $siclLocationSubtInt = 1;
        } elseif ($siclVerifyInt === 2) { // Middle of multi-bytes character.
            for ($siclLoopInt = 2; $bytes >= $siclLoopInt; $siclLoopInt++) { // Seeking for head byte.
                if ((ord($str[$bytes - $siclLoopInt]) >> 6) !== 2) {
                    break;
                }
            }
            switch (ord($str[$bytes - $siclLoopInt]) >> 4) { // Identify the length of current character.
                case 12:
                case 13:
                    $siclVerifiedLengthInt = 2;
                    break;
                case 14:
                    $siclVerifiedLengthInt = 3;
                    break;
                case 15:
                    $siclVerifiedLengthInt = 4;
                    break;
            }
            if ($siclLoopInt === $siclVerifiedLengthInt) // The byte we're verifying is the last byte of the character.
            {
                $siclLocationSubtInt = 0;
            } else {
                $siclLocationSubtInt = $siclLoopInt;
            }
            unset($siclLoopInt, $siclVerifiedLengthInt);
        }
        $siclSlicedStr = substr($str, 0, $bytes - $siclLocationSubtInt);
        unset($str, $bytes, $siclLocationSubtInt);

        return $siclSlicedStr . $tail;
    }

    static function lessenAsEm($str, $ems, $tail = '...') {
        if (function_exists('mb_strimwidth')) {
            return mb_strimwidth($str, 0, $ems - 1, $tail, 'utf-8');
        }
        if (Utils_Unicode::lengthAsEm($str) <= $ems) {
            $tail = '';
        } else {
            $ems -= strlen($tail);
        }
        $len = strlen($str);
        for ($i = $adapted = 0; $i < $len; $adapted = $i) {
            $high = ord($str{$i});
            if ($high < 0x80) {
                $i += 1;
                $ems -= 1;
            } else {
                if ($high < 0xE0) {
                    $i += 2;
                } else {
                    if ($high < 0xF0) {
                        $i += 3;
                    } else {
                        $i += 4;
                    }
                }
                $ems -= 2;
            }
            if ($ems < 0) {
                break;
            }
        }
        return trim(substr($str, 0, $adapted)) . $tail;
    }
}

?>
