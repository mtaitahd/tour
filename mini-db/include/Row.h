#ifndef ROW_H
#define ROW_H

#include <string>

class Row {
public:
    int id;
    std::string data;

    Row() : id(0), data("") {}
    Row(int id, const std::string& data) : id(id), data(data) {}
};

#endif
