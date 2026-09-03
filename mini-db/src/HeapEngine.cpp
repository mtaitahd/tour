#include "../include/HeapEngine.h"

void HeapEngine::insert(const Key& key, const Row& row) {
    data.push_back({key, row});
}

Row* HeapEngine::find(const Key& key) {
    for (auto& kv : data) {
        if (kv.first == key)
            return &kv.second;
    }
    return nullptr;
}

bool HeapEngine::remove(const Key& key) {
    for (auto it = data.begin(); it != data.end(); ++it) {
        if (it->first == key) {
            data.erase(it);
            return true;
        }
    }
    return false;
}

std::string HeapEngine::name() const {
    return "HeapEngine (Linear Scan)";
}
