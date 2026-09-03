#ifndef BTREE_H
#define BTREE_H

#include "BTreeNode.h"
#include "Key.h"
#include "Row.h"
#include <map>

class BTree {
private:
    std::map<Key, Row> entries;
    int order;

public:
    BTree(int order);
    ~BTree();

    void insert(const Key& key, const Row& row);
    BTreeNode* search(const Key& key);
    bool remove(const Key& key);
    void traverse() const;
};

#endif
