import { useState, useEffect } from 'react';
import {
  View,
  Text,
  FlatList,
  TouchableOpacity,
  StyleSheet,
  ActivityIndicator,
} from 'react-native';
import { api } from '../../api/client';

export default function CatalogScreen({ navigation }) {
  const [cities, setCities] = useState([]);
  const [categories, setCategories] = useState([]);
  const [selectedCity, setSelectedCity] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadData();
  }, []);

  async function loadData() {
    try {
      const [citiesData, categoriesData] = await Promise.all([
        api('/cities'),
        api('/categories'),
      ]);
      setCities(citiesData);
      setCategories(categoriesData);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  }

  function navigateToMasters(category) {
    navigation.navigate('MastersList', {
      category,
      cityId: selectedCity,
    });
  }

  if (loading) {
    return (
      <View style={styles.center}>
        <ActivityIndicator size="large" />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.cityRow}>
        {cities.map((city) => (
          <TouchableOpacity
            key={city.id}
            style={[styles.cityChip, selectedCity === city.id && styles.cityChipActive]}
            onPress={() => setSelectedCity(city.id === selectedCity ? null : city.id)}
          >
            <Text
              style={[styles.cityText, selectedCity === city.id && styles.cityTextActive]}
            >
              {city.name_ru}
            </Text>
          </TouchableOpacity>
        ))}
      </View>

      <FlatList
        data={categories}
        numColumns={2}
        contentContainerStyle={styles.grid}
        columnWrapperStyle={styles.gridRow}
        renderItem={({ item }) => (
          <TouchableOpacity
            style={styles.categoryCard}
            onPress={() => navigateToMasters(item)}
          >
            <Text style={styles.categoryIcon}>{item.icon_url || '🔧'}</Text>
            <Text style={styles.categoryName}>{item.name_ru}</Text>
          </TouchableOpacity>
        )}
        keyExtractor={(item) => String(item.id)}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f5f5f5' },
  center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  cityRow: { flexDirection: 'row', flexWrap: 'wrap', padding: 12, gap: 8 },
  cityChip: {
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 20,
    backgroundColor: '#fff',
    borderWidth: 1,
    borderColor: '#ddd',
  },
  cityChipActive: { backgroundColor: '#007AFF', borderColor: '#007AFF' },
  cityText: { fontSize: 14, color: '#333' },
  cityTextActive: { color: '#fff' },
  grid: { padding: 12 },
  gridRow: { justifyContent: 'space-between', marginBottom: 12 },
  categoryCard: {
    flex: 1,
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 20,
    alignItems: 'center',
    marginHorizontal: 4,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 3,
  },
  categoryIcon: { fontSize: 32, marginBottom: 8 },
  categoryName: { fontSize: 14, fontWeight: '600', textAlign: 'center' },
});
