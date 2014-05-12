/* 
 * File:   Utilities.cpp
 * Author: Ismael
 * 
 * Created on 10 de mayo de 2014, 12:31 PM
 */


#include "Utilities.h"

Utilities::Utilities() {
}

Utilities::Utilities(const Utilities& orig) {
}

Utilities::~Utilities() {
}

std::string Utilities::char_to_string(unsigned char *rand_bytes) {

    char const hex_chars[16] = {'0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F'};

    std::ostringstream hex_str;
    for (int i = 0; i < sizeof (rand_bytes); i++) {
        const char ch = rand_bytes[i];
        hex_str << hex_chars[(ch & 0xF0) >> 4] << hex_chars[(ch & 0xF)];
    }

    return hex_str.str();

}

std::string Utilities::get_unique_filename(std::string basepath) {

    unsigned char rand_bytes[32];
    
    RAND_bytes(rand_bytes, sizeof(rand_bytes));

    std::ostringstream filename;

    filename << basepath << Utilities::char_to_string(rand_bytes) << ".fifo";

    return filename.str();

}
