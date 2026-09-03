#ifndef HEAPENGINE_H
#define HEAPENGINE_H

#include "StorageEngine.h"
#include <vector>

class HeapEngine : public StorageEngine {
private:
    std::vector<std::pair<Key, Row>> data;

public:
    void insert(const Key& key, const Row& row) override;
    Row* find(const Key& key) override;
    bool remove(const Key& key) override;
    std::string name() const override;
};

#endif
