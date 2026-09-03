#ifndef STORAGEENGINE_H
#define STORAGEENGINE_H

#include "Row.h"
#include "Key.h"
#include <string>

class StorageEngine {
public:
    virtual ~StorageEngine() = default;
    virtual void insert(const Key& key, const Row& row) = 0;
    virtual Row* find(const Key& key) = 0;
    virtual bool remove(const Key& key) = 0;
    virtual std::string name() const = 0;
};

#endif
