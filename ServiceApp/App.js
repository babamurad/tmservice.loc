import { ActivityIndicator, View, StyleSheet } from 'react-native';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
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

const AuthStackNav = createNativeStackNavigator();
const CatalogStackNav = createNativeStackNavigator();
const ProfileStackNav = createNativeStackNavigator();
const Tab = createBottomTabNavigator();

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
    <CatalogStackNav.Navigator>
      <CatalogStackNav.Screen name="CatalogHome" component={CatalogScreen} options={{ title: 'Каталог' }} />
      <CatalogStackNav.Screen name="MastersList" component={MastersListScreen} />
      <CatalogStackNav.Screen name="MasterDetail" component={MasterDetailScreen} options={{ title: 'Мастер' }} />
    </CatalogStackNav.Navigator>
  );
}

function ProfileStack() {
  return (
    <ProfileStackNav.Navigator>
      <ProfileStackNav.Screen name="MyProfile" component={MyProfileScreen} options={{ title: 'Профиль' }} />
      <ProfileStackNav.Screen name="EditProfile" component={EditProfileScreen} options={{ title: 'Редактировать' }} />
      <ProfileStackNav.Screen name="MyQR" component={MyQRScreen} options={{ title: 'Мой QR-код' }} />
    </ProfileStackNav.Navigator>
  );
}

function MainTabs() {
  return (
    <Tab.Navigator>
      <Tab.Screen name="Catalog" component={CatalogStack} options={{ headerShown: false, title: 'Каталог' }} />
      <Tab.Screen name="Search" component={SearchScreen} options={{ title: 'Поиск' }} />
      <Tab.Screen name="Profile" component={ProfileStack} options={{ headerShown: false, title: 'Профиль' }} />
    </Tab.Navigator>
  );
}

function RootNavigator() {
  const { user, loading } = useAuth();

  if (loading) {
    return (
      <View style={styles.loading}>
        <ActivityIndicator size="large" />
      </View>
    );
  }

  return (
    <NavigationContainer>
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
  loading: { flex: 1, justifyContent: 'center', alignItems: 'center' },
});
