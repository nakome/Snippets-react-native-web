import { useRef } from "react";
import { useParams } from "react-router-native";
import {
  Text,
  View,
  Button,
  Picker,
  TextInput,
  StyleSheet,
  SafeAreaView,
  ScrollView,
  ActivityIndicator,
  FlatList,
} from "react-native";

import { useEffect, useState, useContext } from "react";
import { Ionicons } from "@expo/vector-icons";
// context
import ThemeOptions from "../services/ThemeOptions";
// theme options
import { ThemeStyleColors } from "../config/Theme";
// categories
import Categories from "../config/Categories";
// services
import { getDb } from "../services/Store";
// components
import FlatListItem from "../components/FlatListItem";

export default function Search() {
  // params
  const params = useParams();
  // scrollref
  const scrollRef = useRef(null);
  // config
  const config = useContext(ThemeOptions);
  // loaded
  const [loaded, setLoaded] = useState(false);
  // set values
  const [filter, setFilter] = useState(params.filter.toLowerCase());
  const [title, setTitle] = useState(
    params.val.replace(/^\w/, (c) => c.toUpperCase())
  );
  // limit
  const [limit, setLimit] = useState(5);
  // data
  const [data, setData] = useState({});
  // btn search
  const handleBtnSearch = () => asyncGetSearch();
  // Search
  const asyncGetSearch = async () => {
    setLoaded(false);
    try {
      const response = await getDb(
        "snippets",
        `${filter}=${title}&limit=${limit}`
      );
      setLimit(limit + 3);
      setData(response);
    } catch (e) {
      console.log(e);
    } finally {
      setLoaded(true);
    }
  };

  const handleLoadMore = () =>
    asyncGetSearch().then(() =>
      scrollRef.current.scrollToEnd({ animated: false })
    );

  useEffect(() => asyncGetSearch(), []);

  if (!loaded) {
    return (
      <View style={[styles.container, styles.horizontal]}>
        <ActivityIndicator size="large" color={ThemeStyleColors.warning} />
      </View>
    );
  }

  return (
    loaded && (
      <SafeAreaView style={styles.container}>
        <Picker
          selectedValue={filter}
          style={[styles.picker, { marginTop: 10 }]}
          onValueChange={(itemValue, itemIndex) => setFilter(itemValue)}
        >
          <Picker.Item label="Title" value="title" />
          <Picker.Item label="Author" value="author" />
          <Picker.Item label="Updated" value="updated" />
          <Picker.Item label="Created" value="created" />
          <Picker.Item label="Category" value="category" />
        </Picker>
        {filter === "category" ? (
          <Picker
            selectedValue={title}
            style={styles.picker}
            onValueChange={(itemValue, itemIndex) => setTitle(itemValue)}
          >
            {Categories.map((item, index) => (
              <Picker.Item
                key={`id_${index.toString()}`}
                label={item}
                value={item}
              />
            ))}
          </Picker>
        ) : (
          <TextInput
            maxLength={40}
            style={styles.input}
            onChangeText={(txt) => setTitle(txt)}
            value={title}
          />
        )}

        <View style={styles.buttons}>
          <Button
            color={ThemeStyleColors.primary}
            title={[
              <Ionicons
                name="search"
                size={16}
                color={ThemeStyleColors.warning}
                style={{ marginRight: 5 }}
              />,
              config.lang.search,
            ]}
            onPress={handleBtnSearch}
          />
        </View>

        <ScrollView ref={scrollRef}>
          {data.length > 0 ? (
            <FlatList
              data={data}
              renderItem={({ item, index }) => (
                <FlatListItem key={`id_${index.toString()}`} data={item} />
              )}
              keyExtractor={(item) => item.uid}
            />
          ) : (
            <View style={styles.info}>
              <Text style={styles.infoTitle}>{config.lang.noResults}</Text>
            </View>
          )}
        </ScrollView>
        <Button
          color={ThemeStyleColors.primary}
          title={config.lang.loadMore || ""}
          onPress={handleLoadMore}
        />
      </SafeAreaView>
    )
  );
}

const styles = StyleSheet.create({
  horizontal: {
    flexDirection: "row",
    justifyContent: "space-around",
    padding: 10,
  },
  container: {
    flex: 1,
    justifyContent: "center",
    padding: 10,
    width: "100%",
    maxWidth: 768,
  },
  input: {
    height: 40,
    marginLeft: 12,
    marginRight: 12,
    marginBottom: 12,
    borderWidth: 1,
    padding: 10,
    backgroundColor: ThemeStyleColors.primary,
    borderColor: ThemeStyleColors.info,
    color: ThemeStyleColors.info,
  },
  picker: {
    height: 40,
    marginLeft: 12,
    marginRight: 12,
    marginBottom: 12,
    borderWidth: 1,
    padding: 5,
    backgroundColor: ThemeStyleColors.primary,
    borderColor: ThemeStyleColors.info,
    color: ThemeStyleColors.info,
  },
  content: {
    marginLeft: 12,
    marginRight: 12,
    marginBottom: 12,
    borderWidth: 1,
    padding: 10,
    backgroundColor: ThemeStyleColors.primary,
    borderColor: ThemeStyleColors.info,
    color: ThemeStyleColors.info,
  },
  buttons: {
    margin: 12,
    marginTop: 0,
    display: "flex",
    flexDirection: "row",
    justifyContent: "space-between",
    maxWidth: 150,
  },
  noResults: {
    fontSize: 16,
    margin: 12,
    color: ThemeStyleColors.warning,
  },
  info: {
    backgroundColor: ThemeStyleColors.danger,
    borderColor: ThemeStyleColors.danger,
    borderRadius: 4,
    borderWidth: 1,
    padding: 10,
    margin: 12,
  },
  infoTitle: {
    fontSize: 18,
    margin: 12,
    color: ThemeStyleColors.light,
  },
});
