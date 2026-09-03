#ifndef QUERYPLANNER_H
#define QUERYPLANNER_H

#include "StorageEngine.h"
#include <memory>

class QueryPlanner {
private:
    StorageEngine* engine;

public:
    QueryPlanner();
    void setEngine(StorageEngine* e);
    void executeInsert(const Key& key, const Row& row);
    Row* executeFind(const Key& key);
    bool executeRemove(const Key& key);
    std::string currentEngine() const;
};

#endif
