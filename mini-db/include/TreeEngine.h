#ifndef TREEENGINE_H
#define TREEENGINE_H

#include "StorageEngine.h"
#include "BTree.h"

class TreeEngine : public StorageEngine {
private:
    BTree tree;

public:
    TreeEngine(int order = 4);
    void insert(const Key& key, const Row& row) override;
    Row* find(const Key& key) override;
    bool remove(const Key& key) override;
    std::string name() const override;
};

#endif
