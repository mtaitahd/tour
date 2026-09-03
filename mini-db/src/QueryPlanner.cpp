#include "../include/QueryPlanner.h"
#include <iostream>

QueryPlanner::QueryPlanner() : engine(nullptr) {}

void QueryPlanner::setEngine(StorageEngine* e) {
    engine = e;
    std::cout << "[QueryPlanner] Engine switched to: " << engine->name() << "\n";
}

void QueryPlanner::executeInsert(const Key& key, const Row& row) {
    if (!engine) { std::cerr << "No engine set!\n"; return; }
    engine->insert(key, row);
}

Row* QueryPlanner::executeFind(const Key& key) {
    if (!engine) { std::cerr << "No engine set!\n"; return nullptr; }
    return engine->find(key);
}

bool QueryPlanner::executeRemove(const Key& key) {
    if (!engine) { std::cerr << "No engine set!\n"; return false; }
    return engine->remove(key);
}

std::string QueryPlanner::currentEngine() const {
    return engine ? engine->name() : "None";
}
