import { useParams, useNavigate } from "react-router-native";
import {
  Text,
  View,
  Button,
  StyleSheet,
  SafeAreaView,
  ScrollView,
  ActivityIndicator,
} from "react-native";
import { useEffect, useState, useContext } from "react";
import { Ionicons } from "@expo/vector-icons";
// context
import ThemeOptions from "../services/ThemeOptions";
// theme options
import { ThemeStyleColors } from "../config/Theme";
// services
import { getDb } from "../services/Store";
// utils
import { decodeUnicode } from "../utils/base64";

export default function Preview() {
  const params = useParams();
  const navigate = useNavigate();

  const config = useContext(ThemeOptions);
  const [loaded, setLoaded] = useState({});
  const [data, setData] = useState({});
  const [content, setContent] = useState("");

  const LoadInitialData = async () => {
    setLoaded(false);
    try {
      const response = await getDb("snippets", `uid=${params.id}`);
      setData(response);
      let content = JSON.parse(response.content);
      setContent(decodeUnicode(content.snippet));
    } catch (e) {
      console.log(e);
    } finally {
      setLoaded(true);
    }
  };

  const handleBtnEdit = () => navigate(`/edit/${params.id}`);
  const handleBtnDelete = () => navigate(`/delete/${params.id}`);

  useEffect(LoadInitialData, []);

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
        <ScrollView>
          <View style={styles.row}>
            <Text style={styles.title}>{data.title}</Text>
            <Text style={styles.desc}>{data.description}</Text>

            <Text style={styles.info}>
              {config.lang.category}: {data.category}
            </Text>
            <Text style={styles.info}>
              {config.lang.author}: {data.author}
            </Text>

            <Text style={styles.content}>{content}</Text>
            <View style={styles.buttons}>
              <Button
                color={ThemeStyleColors.primary}
                title={[
                  <Ionicons
                    name="create"
                    size={16}
                    color={ThemeStyleColors.warning}
                    style={{ marginRight: 5 }}
                  />,
                  config.lang.edit,
                ]}
                onPress={handleBtnEdit}
              />
              <Button
                color={ThemeStyleColors.danger}
                title={[
                  <Ionicons
                    name="trash"
                    size={16}
                    color={ThemeStyleColors.warning}
                    style={{ marginRight: 5 }}
                  />,
                  config.lang.delete,
                ]}
                onPress={handleBtnDelete}
              />
            </View>
          </View>
        </ScrollView>
      </SafeAreaView>
    )
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    width: "100%",
    maxWidth: 768,
    userSelect:'none'
  },
  row: {
    padding: 10,
  },
  horizontal: {
    flexDirection: "row",
    justifyContent: "space-around",
    padding: 10,
  },
  title: {
    fontSize: 20,
    color: ThemeStyleColors.warning,
  },
  desc: {
    fontSize: 18,
    color: ThemeStyleColors.light,
  },
  info: {
    marginTop: 10,
    fontSize: 16,
    color: ThemeStyleColors.info,
  },
  content: {
    marginTop: 10,
    fontSize: 14,
    padding: 10,
    lineHeight: 22,
    borderWidth: 2,
    userSelect:'text',
    borderColor: ThemeStyleColors.info,
    borderStyle: "solid",
    backgroundColor: ThemeStyleColors.primary,
    color: ThemeStyleColors.warning,
  },
  buttons: {
    marginTop: 12,
    display: "flex",
    flexDirection: "row",
    justifyContent: "space-between",
    maxWidth: 200,
  },
});
