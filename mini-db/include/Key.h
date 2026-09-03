#ifndef KEY_H
#define KEY_H

class Key {
public:
    int value;

    Key() : value(0) {}
    explicit Key(int v) : value(v) {}

    bool operator<(const Key& other) const { return value < other.value; }
    bool operator>(const Key& other) const { return value > other.value; }
    bool operator==(const Key& other) const { return value == other.value; }
    bool operator<=(const Key& other) const { return value <= other.value; }
    bool operator>=(const Key& other) const { return value >= other.value; }
};

#endif
