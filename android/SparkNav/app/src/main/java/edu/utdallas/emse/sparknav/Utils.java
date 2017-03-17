package edu.utdallas.emse.sparknav;

import android.util.Base64;

/**
 * Created by samiul on 3/14/17.
 */

public class Utils {
    private Utils() {}  // static functions only

    private static final char[] HEX = "0123456789ABCDEF".toCharArray();

    static byte[] base64Decode(String s) {
        return Base64.decode(s, Base64.DEFAULT);
    }

    static String base64Encode(byte[] b) {
        return Base64.encodeToString(b, Base64.DEFAULT).trim();
    }

    static String toHexString(byte[] bytes) {
        char[] chars = new char[bytes.length * 2];
        for (int i = 0; i < bytes.length; i++) {
            int c = bytes[i] & 0xFF;
            chars[i * 2] = HEX[c >>> 4];
            chars[i * 2 + 1] = HEX[c & 0x0F];
        }
        return new String(chars).toLowerCase();
    }
}
