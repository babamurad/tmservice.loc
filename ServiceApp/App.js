import { ActivityIndicator, View, StyleSheet } from 'react-native';
import { NavigationContainer, DefaultTheme } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { Ionicons } from '@expo/vector-icons';
import { AuthProvider, useAuth } from './src/hooks/useAuth';
import LoginScreen from './src/screens/auth/LoginScreen';
import RegisterScreen from './src/screens/auth/RegisterScreen';
import CatalogScreen from './src/screens/catalog/CatalogScreen';
import MastersListScreen from './src/screens/catalog/MastersListScreen';
import MasterDetailScreen from './src/screens/catalog/MasterDetailScreen';
import SearchScreen from './src/screens/search/SearchScreen';
import MyProfileScreen from './src/screens/profile/MyProfileScreen';
import EditProfileScreen from './src/screens/profile/EditProfileScreen';
import MyQRScreen from './src/screens/profile/MyQRScreen';
import { colors } from './src/theme';

const AuthStackNav = createNativeStackNavigator();
const CatalogStackNav = createNativeStackNavigator();
const SearchStackNav = createNativeStackNavigator();
const ProfileStackNav = createNativeStackNavigator();
const Tab = createBottomTabNavigator();

const navigationTheme = {
  ...DefaultTheme,
  colors: {
    ...DefaultTheme.colors,
    primary: colors.primary,
    background: colors.bg,
    card: colors.surface,
    border: colors.border,
    text: colors.ink,
  },
};

const stackScreenOptions = {
  headerStyle: { backgroundColor: colors.surface },
  headerTintColor: colors.ink,
  headerTitleStyle: { fontWeight: '700' },
  headerShadowVisible: false,
  contentStyle: { backgroundColor: colors.bg },
};

function AuthStack() {
  return (
    <AuthStackNav.Navigator screenOptions={{ headerShown: false }}>
      <AuthStackNav.Screen name="Login" component={LoginScreen} />
      <AuthStackNav.Screen name="Register" component={RegisterScreen} />
    </AuthStackNav.Navigator>
  );
}

function CatalogStack() {
  return (
    <CatalogStackNav.Navigator screenOptions={stackScreenOptions}>
      <CatalogStackNav.Screen name="CatalogHome" component={CatalogScreen} options={{ title: 'Каталог' }} />
      <CatalogStackNav.Screen name="MastersList" component={MastersListScreen} />
      <CatalogStackNav.Screen name="MasterDetail" component={MasterDetailScreen} options={{ title: 'Мастер' }} />
    </CatalogStackNav.Navigator>
  );
}

function SearchStack() {
  return (
    <SearchStackNav.Navigator screenOptions={stackScreenOptions}>
      <SearchStackNav.Screen name="SearchHome" component={SearchScreen} options={{ title: 'Поиск' }} />
      <SearchStackNav.Screen name="MasterDetail" component={MasterDetailScreen} options={{ title: 'Мастер' }} />
    </SearchStackNav.Navigator>
  );
}

function ProfileStack() {
  return (
    <ProfileStackNav.Navigator screenOptions={stackScreenOptions}>
      <ProfileStackNav.Screen name="MyProfile" component={MyProfileScreen} options={{ title: 'Профиль' }} />
      <ProfileStackNav.Screen name="EditProfile" component={EditProfileScreen} options={{ title: 'Редактировать' }} />
      <ProfileStackNav.Screen name="MyQR" component={MyQRScreen} options={{ title: 'Мой QR-код' }} />
    </ProfileStackNav.Navigator>
  );
}

const TAB_ICONS = {
  Catalog: 'grid',
  Search: 'search',
  Profile: 'person',
};

function MainTabs() {
  return (
    <Tab.Navigator
      screenOptions={({ route }) => ({
        tabBarIcon: ({ focused, size }) => (
          <Ionicons
            name={focused ? TAB_ICONS[route.name] : `${TAB_ICONS[route.name]}-outline`}
            size={size}
            color={focused ? colors.primary : colors.inkFaint}
          />
        ),
        tabBarActiveTintColor: colors.primary,
        tabBarInactiveTintColor: colors.inkFaint,
        tabBarStyle: { backgroundColor: colors.surface, borderTopColor: colors.border },
      })}
    >
      <Tab.Screen name="Catalog" component={CatalogStack} options={{ headerShown: false, title: 'Каталог' }} />
      <Tab.Screen name="Search" component={SearchStack} options={{ headerShown: false, title: 'Поиск' }} />
      <Tab.Screen name="Profile" component={ProfileStack} options={{ headerShown: false, title: 'Профиль' }} />
    </Tab.Navigator>
  );
}

function RootNavigator() {
  const { user, loading } = useAuth();

  if (loading) {
    return (
      <View style={styles.loading}>
        <ActivityIndicator size="large" color={colors.primary} />
      </View>
    );
  }

  return (
    <NavigationContainer theme={navigationTheme}>
      {user ? <MainTabs /> : <AuthStack />}
    </NavigationContainer>
  );
}

export default function App() {
  return (
    <AuthProvider>
      <RootNavigator />
    </AuthProvider>
  );
}

const styles = StyleSheet.create({
  loading: { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: colors.bg },
});
