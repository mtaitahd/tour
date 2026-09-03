#include "../include/TreeEngine.h"

TreeEngine::TreeEngine(int order) : tree(order) {}

void TreeEngine::insert(const Key& key, const Row& row) {
    tree.insert(key, row);
}

Row* TreeEngine::find(const Key& key) {
    BTreeNode* node = tree.search(key);
    if (!node) return nullptr;
    for (int i = 0; i < (int)node->keys.size(); i++) {
        if (node->keys[i] == key)
            return &node->rows[i];
    }
    return nullptr;
}

bool TreeEngine::remove(const Key& key) {
    return tree.remove(key);
}

std::string TreeEngine::name() const {
    return "TreeEngine (B-Tree Index)";
}
