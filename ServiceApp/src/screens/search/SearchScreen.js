import { useState, useEffect } from 'react';
import {
  View,
  Text,
  TextInput,
  FlatList,
  TouchableOpacity,
  StyleSheet,
  ActivityIndicator,
} from 'react-native';
import { api } from '../../api/client';
import { getCachedDirectory } from '../../api/directoryCache';

export default function SearchScreen({ navigation }) {
  const [query, setQuery] = useState('');
  const [cities, setCities] = useState([]);
  const [categories, setCategories] = useState([]);
  const [selectedCity, setSelectedCity] = useState(null);
  const [selectedCategory, setSelectedCategory] = useState(null);
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);
  const [searched, setSearched] = useState(false);

  useEffect(() => {
    (async () => {
      const [citiesCache, categoriesCache] = await Promise.all([
        getCachedDirectory('cities'),
        getCachedDirectory('categories'),
      ]);
      if (citiesCache) setCities(citiesCache);
      if (categoriesCache) setCategories(categoriesCache);
    })();
  }, []);

  async function runSearch() {
    if (!query.trim() && !selectedCity && !selectedCategory) {
      setResults([]);
      setSearched(false);
      return;
    }

    setLoading(true);
    setSearched(true);

    try {
      const params = new URLSearchParams();
      if (query.trim()) params.append('q', query.trim());
      if (selectedCity) params.append('city_id', selectedCity);
      if (selectedCategory) params.append('category_id', selectedCategory);

      const data = await api(`/masters?${params}`);
      setResults(data.data);
    } catch (err) {
      console.error(err);
      setResults([]);
    } finally {
      setLoading(false);
    }
  }

  function toggleCity(cityId) {
    setSelectedCity((prev) => (prev === cityId ? null : cityId));
  }

  function toggleCategory(categoryId) {
    setSelectedCategory((prev) => (prev === categoryId ? null : categoryId));
  }

  return (
    <View style={styles.container}>
      <TextInput
        style={styles.input}
        placeholder="Например: сантехник, ремонт крана"
        value={query}
        onChangeText={setQuery}
        onSubmitEditing={runSearch}
        returnKeyType="search"
      />

      {cities.length > 0 && (
        <View style={styles.filterRow}>
          {cities.map((city) => (
            <TouchableOpacity
              key={city.id}
              style={[styles.chip, selectedCity === city.id && styles.chipActive]}
              onPress={() => toggleCity(city.id)}
            >
              <Text style={[styles.chipText, selectedCity === city.id && styles.chipTextActive]}>
                {city.name_ru}
              </Text>
            </TouchableOpacity>
          ))}
        </View>
      )}

      {categories.length > 0 && (
        <View style={styles.filterRow}>
          {categories.map((category) => (
            <TouchableOpacity
              key={category.id}
              style={[styles.chip, selectedCategory === category.id && styles.chipActive]}
              onPress={() => toggleCategory(category.id)}
            >
              <Text style={[styles.chipText, selectedCategory === category.id && styles.chipTextActive]}>
                {category.name_ru}
              </Text>
            </TouchableOpacity>
          ))}
        </View>
      )}

      <TouchableOpacity style={styles.searchButton} onPress={runSearch}>
        <Text style={styles.searchButtonText}>Найти</Text>
      </TouchableOpacity>

      {loading ? (
        <View style={styles.center}>
          <ActivityIndicator size="large" />
        </View>
      ) : (
        <FlatList
          data={results}
          contentContainerStyle={styles.list}
          renderItem={({ item }) => (
            <TouchableOpacity
              style={styles.card}
              onPress={() => navigation.navigate('MasterDetail', { master: item })}
            >
              <View style={styles.cardHeader}>
                <Text style={styles.name}>{item.category?.name_ru || 'Мастер'}</Text>
                <View
                  style={[styles.dot, { backgroundColor: item.is_free ? '#34C759' : '#FF3B30' }]}
                />
              </View>
              <Text style={styles.bio} numberOfLines={2}>
                {item.bio || 'Нет описания'}
              </Text>
              {item.city && <Text style={styles.meta}>📍 {item.city.name_ru}</Text>}
            </TouchableOpacity>
          )}
          keyExtractor={(item) => String(item.id)}
          ListEmptyComponent={
            searched ? (
              <View style={styles.center}>
                <Text style={styles.empty}>Ничего не найдено</Text>
              </View>
            ) : null
          }
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f5f5f5' },
  center: { flex: 1, justifyContent: 'center', alignItems: 'center', paddingTop: 40 },
  input: {
    margin: 12,
    marginBottom: 8,
    backgroundColor: '#fff',
    borderRadius: 10,
    borderWidth: 1,
    borderColor: '#ddd',
    paddingHorizontal: 14,
    paddingVertical: 10,
    fontSize: 15,
  },
  filterRow: { flexDirection: 'row', flexWrap: 'wrap', paddingHorizontal: 12, gap: 8, marginBottom: 4 },
  chip: {
    paddingHorizontal: 14,
    paddingVertical: 6,
    borderRadius: 20,
    backgroundColor: '#fff',
    borderWidth: 1,
    borderColor: '#ddd',
  },
  chipActive: { backgroundColor: '#007AFF', borderColor: '#007AFF' },
  chipText: { fontSize: 13, color: '#333' },
  chipTextActive: { color: '#fff' },
  searchButton: {
    marginHorizontal: 12,
    marginVertical: 12,
    backgroundColor: '#007AFF',
    borderRadius: 10,
    paddingVertical: 12,
    alignItems: 'center',
  },
  searchButtonText: { color: '#fff', fontSize: 15, fontWeight: '600' },
  list: { padding: 12, paddingTop: 0 },
  card: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 16,
    marginBottom: 12,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 3,
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  name: { fontSize: 16, fontWeight: '600' },
  dot: { width: 12, height: 12, borderRadius: 6 },
  bio: { fontSize: 14, color: '#666', marginBottom: 6 },
  meta: { fontSize: 12, color: '#999' },
  empty: { fontSize: 16, color: '#999' },
});
