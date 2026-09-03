#include "../include/BTree.h"
#include <iostream>

BTree::BTree(int order) : order(order) {}

BTree::~BTree() = default;

void BTree::insert(const Key& key, const Row& row) {
    entries[key] = row;
}

BTreeNode* BTree::search(const Key& key) {
    auto it = entries.find(key);
    if (it == entries.end()) {
        return nullptr;
    }

    BTreeNode* node = new BTreeNode(order, true);
    node->keys.push_back(key);
    node->rows.push_back(it->second);
    return node;
}

bool BTree::remove(const Key& key) {
    return entries.erase(key) > 0;
}

void BTree::traverse() const {
    for (const auto& entry : entries) {
        std::cout << "  [" << entry.first.value << ": " << entry.second.data << "]\n";
    }
}
